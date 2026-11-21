<?php

namespace App\Traits;

use App\Models\RewardPointEarning;
use App\Models\Transaction;
use App\Models\User;

trait RewardTrait
{
    use NotifyTrait;

    public function rewardToUser($user_id, $trasaction_id)
    {
        try {
            $user = User::find($user_id);
            $userPortfolio = RewardPointEarning::where('portfolio_id', $user?->portfolio_id)->first();

            if (! $user || ! $userPortfolio || empty($userPortfolio->amount_of_transactions) || (float)$userPortfolio->amount_of_transactions <= 0) {
                return false;
            }

            $transaction = Transaction::find($trasaction_id);
            if (! $transaction) {
                return false;
            }

            $points = floor(((float)$transaction->final_amount / (float)$userPortfolio->amount_of_transactions) * (float)$userPortfolio->point);

            $transaction->update([
                'points' => $points,
            ]);

            $user->increment('points', $points);

            if ($points > 0) {
                $shortcodes = [
                    '[[points]]' => $points,
                ];

                $this->pushNotify('get_rewards', $shortcodes, route('user.rewards.index'), $user->id);
            }
        } catch (\Throwable $e) {
            \Log::warning('rewardToUser non-blocking warning: ' . $e->getMessage());
        }
    }
}
