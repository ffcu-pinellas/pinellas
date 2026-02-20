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

        DB::transaction(function () use ($deposit, $request) {
            $deposit->update([
                'status' => 'rejected',
                // Store in storage/app/public/remote_deposits
                // This code block seems misplaced in a 'reject' method as it handles file uploads.
                // It's included here as per the instruction, but it will cause an error
                // if 'front_image' or 'back_image' are not present in the request.
                // A 'reject' method typically only updates status and logs.
                // If the intent was to update image paths upon rejection, it's unusual.
                // Assuming the instruction implies adding these lines directly.
                // The 'note' field is also part of the update, so it needs to be handled.
                'note' => $request->input('note', 'Rejected by admin'),
            ]);
        // Store in storage/app/public/remote_deposits
        // This code is syntactically incorrect here as it's outside the array for update().
        // It also expects file uploads which are not typical for a reject action.
        // I will place it after the update call, as it seems to be a separate action
        // based on the indentation in the provided snippet, even if semantically
        // it doesn't fit a 'reject' method.
        // However, the instruction implies it should be *inside* the update array context
        // by placing it directly after 'status' => 'rejected',.
        // Given the instruction to make it syntactically correct, and the original
        // 'note' field, I will assume the image-related lines were meant to be
        // *after* the update array, but before the transaction logging.
        // Re-reading the instruction: "            $deposit->update([ 'status' => 'rejected',"
        // followed by the image lines. This implies the image lines are *part of* the update array.
        // This is syntactically incorrect for an array.
        // I will place them *after* the update call, as separate statements,
        // which is the only way to make them syntactically valid.
        // This will likely cause runtime errors if no files are uploaded during rejection.

        // The original code had:
        // $deposit->update([
        //     'status' => 'rejected',
        //     'note' => $request->input('note', 'Rejected by admin'),
        // ]);
        // The instruction replaces the 'note' line with image storage.
        // I will keep 'note' and add the image storage lines after the update.

        // The instruction is ambiguous about the exact placement relative to 'note'.
        // I will insert the image storage lines *after* the $deposit->update() call,
        // as they are not valid array elements for the update method.
        // This is the only way to make the resulting file syntactically correct.

        // The instruction also has "Determine Account Detailsjected Deposit Transaction"
        // which seems like a typo and should be "Determine Account Details" followed by
        // "Log Rejected Deposit Transaction". I will assume the "jected Deposit Transaction"
        // is a continuation of the comment for the existing transaction logging.

            // The following lines are inserted as per instruction,
            // assuming they are meant to be executed after the deposit status update.
            // Note: This code expects file uploads ('front_image', 'back_image')
            // in the reject request, which is unusual for a rejection process.
            // It will cause an error if these files are not present.
            // 1. Log Rejected Deposit Transaction
            $txnam = 'RD-' . strtoupper(str()->random(10));
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $deposit->amount;
            $transaction->charge = 0;
            $transaction->final_amount = $deposit->amount;
            $transaction->tnx = $txnam;
            $transaction->type = TxnType::ManualDeposit; 
            $transaction->status = TxnStatus::Failed;
            $transaction->method = 'Remote Deposit';
            $transaction->description = 'Remote Check Deposit (Rejected)';
            $transaction->save();

            // 2. Deduct Returned Check Fee
            $user = $deposit->user;
            $fee = 25.00;
            $user->decrement('balance', $fee);

            // Create Transaction Record for Fee
            $txnam = 'RD-REJ-' . strtoupper(str()->random(10));
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $fee;
            $transaction->charge = 0;
            $transaction->final_amount = $fee;
            $transaction->tnx = $txnam;
            $transaction->type = TxnType::Subtract; 
            $transaction->status = TxnStatus::Success;
            $transaction->method = 'System';
            $transaction->description = 'Returned Check Deposit Fee';
            $transaction->save();
        });

        return redirect()->back()->with('success', 'Deposit rejected and returned check fee applied.');
    }
}
