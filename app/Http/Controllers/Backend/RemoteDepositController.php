<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RemoteDeposit;
use App\Models\Transaction;
use App\Models\LevelReferral;
use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Traits\NotifyTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RemoteDepositController extends Controller
{
    use NotifyTrait;

    public function index()
    {
        $deposits = RemoteDeposit::with('user')
            ->when(auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin'), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('staff_id', auth()->id());
                });
            })
            ->latest()->paginate(15);
        return view('backend.remote_deposit.index', compact('deposits'));
    }

    public function approve($id)
    {
        $deposit = RemoteDeposit::with('user')->findOrFail($id);
        
        // Security Check
        if (auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
            if ($deposit->user?->staff_id != auth()->id() || !auth()->user()->can('officer-deposit-manage')) {
                abort(403, 'Unauthorized action.');
            }
        }

        if ($deposit->status !== 'pending') {
            return redirect()->back()->with('error', 'Deposit already processed.');
        }

        $txnam = 'RD-' . strtoupper(str()->random(10));
        DB::transaction(function () use ($deposit) {
            $deposit->update(['status' => 'approved']);

            // Update the linked transaction (handles balance addition automatically)
            if ($deposit->transaction_tnx) {
                $transaction = Transaction::where('tnx', $deposit->transaction_tnx)->first();
                if ($transaction) {
                    // Level referral
                    if (setting('deposit_level')) {
                        $level = LevelReferral::where('type', 'deposit')->max('the_order') + 1;
                        creditReferralBonus($transaction->user, 'deposit', $transaction->amount, $level);
                    }

                    \Txn::update($deposit->transaction_tnx, TxnStatus::Success, $deposit->user_id, 'Remote Deposit Approved');

                    // Standard Notifications (Matching DepositController)
                    $shortcodes = [
                        '[[full_name]]' => $transaction->user->full_name,
                        '[[txn]]' => $transaction->tnx,
                        '[[gateway_name]]' => $transaction->method,
                        '[[deposit_amount]]' => $transaction->amount,
                        '[[site_title]]' => setting('site_title', 'global'),
                        '[[site_url]]' => route('home'),
                        '[[message]]' => 'Remote Deposit Approved',
                        '[[status]]' => 'approved',
                    ];
                    $this->mailNotify($transaction->user->email, 'user_manual_deposit_request', $shortcodes);
                    $this->smsNotify('user_manual_deposit_request', $shortcodes, $transaction->user->phone);
                }
            }
        });

        // Send Native Push
        $this->pushNotify('remote_deposit_approved', [
            '[[amount]]' => setting('currency_symbol') . ' ' . number_format($deposit->amount, 2),
            '[[txn]]' => $deposit->transaction_tnx ?? 'N/A',
        ], route('user.remote_deposit'), $deposit->user_id);

        return redirect()->back()->with('success', 'Deposit approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $deposit = RemoteDeposit::with('user')->findOrFail($id);

        // Security Check
        if (auth()->user()->hasAnyRole(['Account Officer', 'Account-Officer'], 'admin') && !auth()->user()->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin')) {
            if ($deposit->user?->staff_id != auth()->id() || !auth()->user()->can('officer-deposit-manage')) {
                abort(403, 'Unauthorized action.');
            }
        }

        if ($deposit->status !== 'pending') {
            return redirect()->back()->with('error', 'Deposit already processed.');
        }

        $txnam = 'RD-' . strtoupper(str()->random(10));
        DB::transaction(function () use ($deposit, $request) {
            $deposit->update([
                'status' => 'rejected',
                'note' => $request->input('note', 'Rejected by admin'),
            ]);

            // Update linked transaction to Failed
            if ($deposit->transaction_tnx) {
                $transaction = Transaction::where('tnx', $deposit->transaction_tnx)->first();
                if ($transaction) {
                    $note = $request->input('note', 'Rejected by admin');
                    \Txn::update($deposit->transaction_tnx, TxnStatus::Failed, $deposit->user_id, $note);

                    // Standard Notifications
                    $shortcodes = [
                        '[[full_name]]' => $transaction->user->full_name,
                        '[[txn]]' => $transaction->tnx,
                        '[[gateway_name]]' => $transaction->method,
                        '[[deposit_amount]]' => $transaction->amount,
                        '[[site_title]]' => setting('site_title', 'global'),
                        '[[site_url]]' => route('home'),
                        '[[message]]' => $note,
                        '[[status]]' => 'Rejected',
                    ];
                    $this->mailNotify($transaction->user->email, 'user_manual_deposit_request', $shortcodes);
                    $this->smsNotify('user_manual_deposit_request', $shortcodes, $transaction->user->phone);
                }
            }
            
            // Deduct Returned Check Fee (Second)
            $user = $deposit->user;
            $fee = 25.00;
            $user->decrement('balance', $fee);

            // Create Transaction Record for Fee
            $txnFeenam = 'RD-REJ-' . strtoupper(str()->random(10));
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $fee;
            $transaction->charge = 0;
            $transaction->final_amount = $fee;
            $transaction->tnx = $txnFeenam;
            $transaction->type = TxnType::Subtract; 
            $transaction->status = TxnStatus::Success;
            $transaction->method = 'System';
            $transaction->description = 'Returned Check Deposit Fee';
            $transaction->save();
        });

        // Send Native Push
        $this->pushNotify('remote_deposit_rejected', [
            '[[amount]]' => setting('currency_symbol') . ' ' . number_format($deposit->amount, 2),
            '[[txn]]' => $deposit->transaction_tnx ?? 'N/A',
            '[[reason]]' => $request->note ?? 'Policy violation or poor image quality.',
        ], route('user.remote_deposit'), $deposit->user_id);

        return redirect()->back()->with('success', 'Deposit rejected and returned check fee applied.');
    }
}
