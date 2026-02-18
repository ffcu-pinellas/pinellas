<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Bill;
use App\Models\BillService;
use App\Models\Transaction;
use App\Enums\TxnType;
use App\Enums\TxnStatus;
use App\Enums\BillStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Facades\Txn\Txn;

class BillPayController extends Controller
{
    public function index()
    {
        $billers = BillService::orderBy('name')->get();
        // Savings logic moved to View using auth()->user()
        return view('frontend::bill_pay.index', compact('billers'));
    }

    public function pay(Request $request)
    {
        $request->validate([
            'biller_id' => 'required|exists:bill_services,id',
            'amount' => 'required|numeric|min:0.01',
            'data' => 'required|array',
            'account_type' => 'required|in:default,savings_primary',
        ]);

        $user = auth()->user();
        $biller = BillService::findOrFail($request->biller_id);
        $walletType = $request->account_type;
        
        if ($walletType === 'savings_primary') {
            $balance = $user->savings_balance;
            $accountLabel = __('Savings');
        } else {
            $balance = $user->balance;
            $accountLabel = __('Checking');
        }

        if ($balance < $request->amount) {
            notify()->error(__('Insufficient Balance'), 'Error');
            return back();
        }

        if ($request->amount < $biller->min_amount || ($biller->max_amount > 0 && $request->amount > $biller->max_amount)) {
            notify()->error(__('Amount out of range'), 'Error');
            return back();
        }

        try {
            DB::transaction(function () use ($request, $user, $biller, $walletType, $accountLabel) {
                // Deduct Balance
                if ($walletType === 'savings_primary') {
                    $user->decrement('savings_balance', $request->amount);
                } else {
                    $user->decrement('balance', $request->amount);
                }

                // Create Bill
                $bill = Bill::create([
                    'bill_service_id' => $biller->id,
                    'user_id' => $user->id,
                    'data' => json_encode($request->data), // Ensure JSON encoding
                    'amount' => $request->amount,
                    'charge' => 0,
                    'status' => 'completed',
                ]);

                // Create Transaction
                $transaction = new Transaction();
                $transaction->user_id = $user->id;
                $transaction->currency = setting('site_currency', 'global');
                $transaction->amount = $request->amount;
                $transaction->charge = 0;
                $transaction->final_amount = $request->amount;
                $transaction->method = $biller->name;
                $transaction->trx = strtoupper(str_replace('.', '', uniqid('', true)));
                $transaction->type = TxnType::PayBill;
                $transaction->status = TxnStatus::Success;
                $transaction->description = __('Bill Payment to :biller from :account', [
                    'biller' => $biller->name,
                    'account' => $accountLabel
                ]);
                $transaction->wallet_type = $walletType;
                $transaction->save();
            });

            notify()->success(__('Bill Payment Successful'), 'Success');
            return to_route('user.bill-pay.index');

        } catch (\Exception $e) {
            notify()->error(__('Something went wrong: ') . $e->getMessage(), 'Error');
            return back();
        }
    }
}
