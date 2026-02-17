<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RemoteDeposit;
use App\Models\Transaction;
use App\Enums\TxnStatus;
use App\Enums\TxnType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RemoteDepositController extends Controller
{
    public function index()
    {
        $deposits = RemoteDeposit::with('user')->latest()->paginate(15);
        return view('backend.remote_deposit.index', compact('deposits'));
    }

    public function approve($id)
    {
        $deposit = RemoteDeposit::findOrFail($id);
        if ($deposit->status !== 'pending') {
            return redirect()->back()->with('error', 'Deposit already processed.');
        }

        DB::transaction(function () use ($deposit) {
            $deposit->update(['status' => 'approved']);

            // Credit the user's main wallet (Checking)
            $user = $deposit->user;
            $user->increment('balance', $deposit->amount);

            // Create Transaction Record
            $txnam = 'RD-' . strtoupper(str()->random(10));
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $deposit->amount;
            $transaction->charge = 0;
            $transaction->final_amount = $deposit->amount;
            $transaction->tnx = $txnam;
            $transaction->type = TxnType::ManualDeposit; // Or create a new Enum for RemoteDeposit
            $transaction->status = TxnStatus::Success;
            $transaction->method = 'Remote Deposit';
            $transaction->description = 'Remote Check Deposit Approved';
            $transaction->save();
        });

        return redirect()->back()->with('success', 'Deposit approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $deposit = RemoteDeposit::findOrFail($id);
        if ($deposit->status !== 'pending') {
            return redirect()->back()->with('error', 'Deposit already processed.');
        }

        $deposit->update([
            'status' => 'rejected',
            'note' => $request->input('note', 'Rejected by admin'),
        ]);

        return redirect()->back()->with('success', 'Deposit rejected.');
    }
}
