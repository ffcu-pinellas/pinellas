<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Http\Controllers\Controller;
use App\Models\RewardPointEarning;
use App\Models\RewardPointRedeem;
use App\Models\Transaction;
use Txn;

class RewardController extends Controller
{
    public function index()
    {
        $redeems = RewardPointRedeem::with('portfolio')->get();
        $earnings = RewardPointEarning::with('portfolio')->get();

        $myPortfolio = RewardPointRedeem::where('portfolio_id', auth()->user()->portfolio_id)->first();

        $transactions = Transaction::where('user_id', auth()->id())
            ->latest()
            ->where('type', TxnType::RewardRedeem)
            ->paginate(5);

        return view('frontend::rewards.index', compact('redeems', 'earnings', 'myPortfolio', 'transactions'));
    }

    public function redeemNow()
    {
        // Get user
        $user = auth()->user();
        $userPoints = $user->points;

        // Get user portfolio redeem
        $portfolio = RewardPointRedeem::where('portfolio_id', $user->portfolio_id)->first();

        // Check portfolio exists or not and user's point euqal or less than 0 then redirect back
        if (! $portfolio || $user->points <= 0) {
            notify()->error(__('You can\'t eligible for redeem.'));

            return back();
        }

        // Calculate redeem amount by portfolio wise redeem point and amount
        $redeemAmount = ($portfolio->amount / $portfolio->point) * $userPoints;

        // Create transaction
        Txn::new($redeemAmount, 0, $redeemAmount, 'System', $userPoints.' Points Reward Redeem', TxnType::RewardRedeem, TxnStatus::Success, '', null, $user->id, null, 'User');

        // Deduct user point
        $user->points = 0;

        // Add amount to user balance
        $user->balance += $redeemAmount;
        $user->save();

        notify()->success(__('Rewards redeem successfully!'));

        return back();
    }
}
