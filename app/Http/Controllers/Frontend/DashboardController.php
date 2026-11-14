<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\DpsStatus;
use App\Enums\FdrStatus;
use App\Enums\LoanStatus;
use App\Http\Controllers\Controller;
use App\Models\LoanTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        try {
            $user = auth()->user();
            $transactions = Transaction::where('user_id', $user->id);

            $recentTransactions = $transactions->latest()->take(10)->get();

            $referral = null;
            try {
                $referral = method_exists($user, 'getReferrals') ? $user->getReferrals()->first() : null;
            } catch (\Throwable $e) {}

            $loan_last_date = null;
            try {
                $activeLoan = $user->loan?->whereIn('status', [LoanStatus::Running, LoanStatus::Due])->first();
                $latestInstallment = $activeLoan ? LoanTransaction::where('loan_id', $activeLoan->id)->latest('installment_date')->first() : null;
                if ($latestInstallment && $latestInstallment->installment_date) {
                    $loan_last_date = $latestInstallment->installment_date instanceof \Carbon\Carbon
                        ? $latestInstallment->installment_date->format('d M Y')
                        : date('d M Y', strtotime((string)$latestInstallment->installment_date));
                }
            } catch (\Throwable $e) {}

            $dataCount = [
                'total_transaction' => (clone $transactions)->count(),
                'total_deposit' => method_exists($user, 'totalDeposit') ? $user->totalDeposit() : 0,
                'total_profit' => method_exists($user, 'totalProfit') ? $user->totalProfit() : 0,
                'profit_last_7_days' => method_exists($user, 'totalProfit') ? $user->totalProfit(7) : 0,
                'total_withdraw' => method_exists($user, 'totalWithdraw') ? $user->totalWithdraw() : 0,
                'total_transfer' => method_exists($user, 'totalTransfer') ? $user->totalTransfer() : 0,
                'total_dps' => $user->dps ? $user->dps->count() : 0,
                'total_fdr' => $user->fdr ? $user->fdr->count() : 0,
                'total_running_dps' => $user->dps ? $user->dps->whereIn('status', [DpsStatus::Running, DpsStatus::Due])->count() : 0,
                'total_running_loan' => $user->loan ? $user->loan->whereIn('status', [LoanStatus::Running, LoanStatus::Due])->count() : 0,
                'total_running_fdr' => $user->fdr ? $user->fdr->where('status', FdrStatus::Running)->count() : 0,
                'loan_last_date' => $loan_last_date,
                'total_loan' => $user->loan ? $user->loan->count() : 0,
                'total_referral_profit' => method_exists($user, 'totalReferralProfit') ? $user->totalReferralProfit() : 0,
                'total_referral' => $referral?->relationships()->count() ?? 0,
                'deposit_bonus' => method_exists($user, 'totalDepositBonus') ? $user->totalDepositBonus() : 0,
                'portfolio_achieved' => method_exists($user, 'portfolioAchieved') ? $user->portfolioAchieved() : 0,
                'total_tickets' => $user->ticket ? $user->ticket->count() : 0,
                'recentTransactions' => $recentTransactions,
                'user' => $user,
                'dps_mature_amount' => $user->dps ? $user->dps->whereIn('status', [DpsStatus::Running, DpsStatus::Due])->sum('total_mature_amount') : 0,
                'fdr_mature_amount' => $user->fdr ? $user->fdr->where('status', FdrStatus::Running)->sum('total_mature_amount') : 0,
                'total_loan_amount' => $user->loan ? $user->loan->whereIn('status', [LoanStatus::Running, LoanStatus::Due])->sum('total_loan_amount') : 0,
                'currency' => setting('currency_symbol', '$'),
            ];

            // Available widgets for sorting
            $user_widgets = ['transactions', 'messages', 'promo', 'bill_pay', 'cards'];
            
            $widgetOrder = $user->dashboard_order ?? $user_widgets;
            
            $dataCount['user_widgets'] = $user_widgets;
            $dataCount['widgetOrder'] = $widgetOrder;

            // Dedicated custom institutional dashboard (e.g. Cleveland State Bank)
            $currentInstitution = app()->bound('institution') ? app('institution') : null;
            if ($currentInstitution && ! $currentInstitution->is_default) {
                $customView = 'frontend::institution.' . $currentInstitution->slug . '_dashboard';
                if (view()->exists($customView)) {
                    $userCard = null;
                    try {
                        $userCard = \App\Models\UserCard::where('user_id', $user->id)->first();
                    } catch (\Throwable $ce) {}
                    $dataCount['userCard'] = $userCard;
                    $dataCount['currentInstitution'] = $currentInstitution;
                    return view($customView, $dataCount);
                }
            }

            return view('frontend::user.dashboard', $dataCount);
        } catch (\Throwable $e) {
            \Log::error("DashboardController::dashboard Exception: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());
            
            // Safe emergency fallback render
            $currentInstitution = app()->bound('institution') ? app('institution') : null;
            $user = auth()->user();
            $dataCount = [
                'user' => $user,
                'currency' => '$',
                'recentTransactions' => collect(),
                'userCard' => null,
                'currentInstitution' => $currentInstitution,
                'total_transaction' => 0,
                'total_deposit' => $user->balance ?? 0,
                'total_profit' => 0,
                'total_withdraw' => 0,
                'total_transfer' => 0,
                'total_dps' => 0,
                'total_fdr' => 0,
                'total_running_dps' => 0,
                'total_running_loan' => 0,
                'total_running_fdr' => 0,
                'loan_last_date' => null,
                'total_loan' => 0,
                'total_referral_profit' => 0,
                'total_referral' => 0,
                'deposit_bonus' => 0,
                'portfolio_achieved' => 0,
                'total_tickets' => 0,
                'dps_mature_amount' => 0,
                'fdr_mature_amount' => 0,
                'total_loan_amount' => 0,
                'user_widgets' => ['transactions', 'messages', 'promo', 'bill_pay', 'cards'],
                'widgetOrder' => ['transactions', 'messages', 'promo', 'bill_pay', 'cards'],
            ];
            
            if ($currentInstitution && ! $currentInstitution->is_default) {
                $customView = 'frontend::institution.' . $currentInstitution->slug . '_dashboard';
                if (view()->exists($customView)) {
                    return view($customView, $dataCount);
                }
            }
            return view('frontend::user.dashboard', $dataCount);
        }
    }

    public function saveOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
        ]);

        $user = auth()->user();
        $user->dashboard_order = $request->order;
        $user->save();

        return response()->json(['status' => 'success']);
    }
}
