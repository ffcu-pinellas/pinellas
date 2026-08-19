<?php

namespace App\Http\Controllers\Backend;

use App\Enums\KYCStatus;
use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Dps;
use App\Models\Fdr;
use App\Models\Loan;
use App\Models\LoginActivities;
use App\Models\ReferralRelationship;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function dashboard()
    {
        try {
            $adminUser = auth('admin')->user();
            $isAccountOfficer = $adminUser && $adminUser->hasRole('Account Officer', 'admin') && !$adminUser->hasAnyRole(['Super-Admin', 'Super Admin'], 'admin');

            $baseTransaction = Transaction::query();
            $baseUser = User::query();

            // Scope queries for Account Officer
            if ($isAccountOfficer) {
                $baseUser->where('staff_id', $adminUser->id);
                $baseTransaction->whereHas('user', function ($q) use ($adminUser) {
                    $q->where('staff_id', $adminUser->id);
                });
            }

            $totalDeposit = (clone $baseTransaction)->where('status', TxnStatus::Success->value)->where('type', TxnType::ManualDeposit->value);

            $totalSend = (clone $baseTransaction)->where('status', TxnStatus::Success->value)
                ->where('type', TxnType::FundTransfer->value)
                ->sum('amount');

            $activeUser = (clone $baseUser)->where('status', 1)->count();
            $disabledUser = (clone $baseUser)->where('status', 0)->count();

            $totalStaff = Admin::count();

            $latestUser = (clone $baseUser)->latest()->take(5)->get();

            $totalWithdraw = (clone $baseTransaction)->where('type', TxnType::Withdraw->value);

            $withdrawCount = (clone $baseTransaction)->where('type', TxnType::Withdraw->value)
                ->where('status', TxnStatus::Pending->value)
                ->count();

            $kycCount = (clone $baseUser)->where('kyc', KYCStatus::Pending->value)->count();

            $depositCount = (clone $baseTransaction)->where('type', TxnType::ManualDeposit->value)
                ->where('status', TxnStatus::Pending->value)
                ->count();

            $totalReferral = ReferralRelationship::whereHas('user', function ($q) use ($isAccountOfficer, $adminUser) {
                if ($isAccountOfficer && $adminUser) $q->where('staff_id', $adminUser->id);
            })->count();

            // ============================= Start dashboard statistics =============================================

            $startDate = request()->start_date ? Carbon::createFromDate(request()->start_date) : Carbon::now()->subDays(7);
            $endDate = request()->end_date ? Carbon::createFromDate(request()->end_date) : Carbon::now();
            $dateArray = array_fill_keys(generate_date_range_array($startDate, $endDate), 0);

            $dateFilter = [request()->start_date ? $startDate : $startDate->subDays(1), $endDate->addDays(1)];

            $depositStatistics = (clone $totalDeposit)->whereBetween('created_at', $dateFilter)->get()->groupBy('day')->map(function ($group) {
                return $group->sum('amount');
            })->toArray();

            $depositStatistics = array_replace($dateArray, $depositStatistics);

            $withdrawStatistics = (clone $totalWithdraw)->whereBetween('created_at', $dateFilter)->get()->groupBy('day')->map(function ($group) {
                return $group->sum('amount');
            })->toArray();
            $withdrawStatistics = array_replace($dateArray, $withdrawStatistics);

            $dpsStatistics = Dps::whereBetween('created_at', $dateFilter)->whereHas('user', function ($q) use ($isAccountOfficer, $adminUser) {
                if ($isAccountOfficer && $adminUser) $q->where('staff_id', $adminUser->id);
            })->get()->groupBy('day')->map(function ($group) {
                return $group->sum('total_dps_amount');
            })->toArray();

            $dpsStatistics = array_replace($dateArray, $dpsStatistics);

            $fdrStatistics = Fdr::whereBetween('created_at', $dateFilter)->whereHas('user', function ($q) use ($isAccountOfficer, $adminUser) {
                if ($isAccountOfficer && $adminUser) $q->where('staff_id', $adminUser->id);
            })->get()->groupBy('day')->map(function ($group) {
                return $group->sum('amount');
            })->toArray();
            $fdrStatistics = array_replace($dateArray, $fdrStatistics);

            $loanStatistics = Loan::whereBetween('created_at', $dateFilter)->whereHas('user', function ($q) use ($isAccountOfficer, $adminUser) {
                if ($isAccountOfficer && $adminUser) $q->where('staff_id', $adminUser->id);
            })->get()->groupBy('day')->map(function ($group) {
                return $group->sum('amount');
            })->toArray();
            $loanStatistics = array_replace($dateArray, $loanStatistics);

            // ============================= End dashboard statistics =============================================

            // set cache for 1 minute
            $loginActivities = Cache::remember('login-activities-' . ($adminUser->id ?? 1), 60, function () use ($isAccountOfficer, $adminUser) {
                $query = LoginActivities::query();
                if ($isAccountOfficer && $adminUser) {
                    $query->whereHas('user', function ($q) use ($adminUser) {
                        $q->where('staff_id', $adminUser->id);
                    });
                }
                return $query->get();
            });

            $browser = $loginActivities->groupBy('browser')->map->count()->toArray();
            $platform = $loginActivities->groupBy('platform')->map->count()->toArray();

            $country = (clone $baseUser)->get()->groupBy('country')->map(function ($c) {
                return $c->count();
            })->toArray();

            arsort($country);
            $country = array_slice($country, 0, 5);

            $symbol = setting('currency_symbol', 'global') ?? '$';
            $total_dps = Dps::whereHas('user', function ($q) use ($isAccountOfficer, $adminUser) {
                if ($isAccountOfficer && $adminUser) $q->where('staff_id', $adminUser->id);
            })->get()->sum('total_dps_amount');
            $total_fdr = Fdr::whereHas('user', function ($q) use ($isAccountOfficer, $adminUser) {
                if ($isAccountOfficer && $adminUser) $q->where('staff_id', $adminUser->id);
            })->sum('amount');
            $total_loan = Loan::whereHas('user', function ($q) use ($isAccountOfficer, $adminUser) {
                if ($isAccountOfficer && $adminUser) $q->where('staff_id', $adminUser->id);
            })->sum('amount');
            $total_bill = 0;

            $fund_transfer_statistics = [
                'Fund Transfer' => (clone $baseTransaction)->where('type', TxnType::FundTransfer->value)->sum('amount'),
                'Fund Wire Transfer' => (clone $baseTransaction)->wireTransfer()->sum('amount'),
            ];

            $totalWithdrawAmount = (clone $baseTransaction)->totalWithdraw()->sum('amount');
            $totalDepositBonus = (clone $baseTransaction)->totalDepositBonus()->sum('amount');

            $data = [
                'withdraw_count' => $withdrawCount,
                'kyc_count' => $kycCount,
                'deposit_count' => $depositCount,

                'register_user' => (clone $baseUser)->count(),
                'active_user' => $activeUser,
                'disabled_user' => $disabledUser,
                'latest_user' => $latestUser,

                'total_staff' => $totalStaff,

                'total_deposit' => (clone $totalDeposit)->sum('amount'),
                'total_dps' => $total_dps,
                'total_fdr' => $total_fdr,
                'total_loan' => $total_loan,
                'total_bill' => $total_bill,
                'points' => (clone $baseUser)->sum('points'),
                'total_send' => $totalSend,
                'total_withdraw' => $totalWithdrawAmount,
                'total_referral' => $totalReferral,

                'date_label' => $dateArray,
                'deposit_statistics' => $depositStatistics,
                'withdraw_statistics' => $withdrawStatistics,
                'dps_statistics' => $dpsStatistics,
                'fdr_statistics' => $fdrStatistics,
                'loan_statistics' => $loanStatistics,
                'bill_statistics' => [],
                'fund_transfer_statistics' => $fund_transfer_statistics,

                'start_date' => isset(request()->start_date) ? $startDate : $startDate->addDays(1)->format('m/d/Y'),
                'end_date' => isset(request()->end_date) ? $endDate : $endDate->subDays(1)->format('m/d/Y'),

                'deposit_bonus' => $totalDepositBonus,
                'total_gateway' => 0,
                'total_ticket' => Ticket::whereHas('user', function ($q) use ($isAccountOfficer, $adminUser) {
                    if ($isAccountOfficer && $adminUser) $q->where('staff_id', $adminUser->id);
                })->count(),

                'browser' => $browser,
                'platform' => $platform,
                'country' => $country,
                'symbol' => $symbol,
            ];

            // Date range filter for statistics
            if (request()->ajax()) {
                return response()->json([
                    'date_label' => $dateArray,
                    'deposit_statistics' => $depositStatistics,
                    'total_dps' => $dpsStatistics,
                    'total_fdr' => $fdrStatistics,
                    'total_loan' => $loanStatistics,
                    'total_bill' => [],
                    'withdraw_statistics' => $withdrawStatistics,
                    'symbol' => $symbol,
                ]);
            }

            return view('backend.dashboard', compact('data'));
        } catch (\Throwable $e) {
            \Log::error('DashboardController error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            // Render basic safe data in emergency fallback
            $data = [
                'withdraw_count' => 0,
                'kyc_count' => 0,
                'deposit_count' => 0,
                'register_user' => User::count(),
                'active_user' => User::where('status', 1)->count(),
                'disabled_user' => User::where('status', 0)->count(),
                'latest_user' => User::latest()->take(5)->get(),
                'total_staff' => Admin::count(),
                'total_deposit' => 0,
                'total_dps' => 0,
                'total_fdr' => 0,
                'total_loan' => 0,
                'total_bill' => 0,
                'points' => 0,
                'total_send' => 0,
                'total_withdraw' => 0,
                'total_referral' => 0,
                'date_label' => [],
                'deposit_statistics' => [],
                'withdraw_statistics' => [],
                'dps_statistics' => [],
                'fdr_statistics' => [],
                'loan_statistics' => [],
                'bill_statistics' => [],
                'fund_transfer_statistics' => [],
                'start_date' => now()->subDays(7)->format('m/d/Y'),
                'end_date' => now()->format('m/d/Y'),
                'deposit_bonus' => 0,
                'total_gateway' => 0,
                'total_ticket' => 0,
                'browser' => [],
                'platform' => [],
                'country' => [],
                'symbol' => '$',
            ];

            return view('backend.dashboard', compact('data'));
        }
    }
}
