<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Deposit;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $data = [
            'register_user' => User::count(),
            'active_user' => User::where('status', 1)->count(),
            'disabled_user' => User::where('status', 0)->count(),
            'total_staff' => \App\Models\Admin::count(),
            'total_deposit' => Deposit::where('status', 1)->sum('amount') ?? 0,
            'total_withdraw' => Withdrawal::where('status', 1)->sum('amount') ?? 0,
            'total_referral' => 0,
            'total_send' => Transaction::sum('amount') ?? 0,
            'total_dps' => 0,
            'total_fdr' => 0,
            'total_loan' => 0,
            'points' => 0,
            'deposit_bonus' => 0,
            'total_ticket' => 0,
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ];

        $currencySymbol = setting('currency_symbol', '$');

        return view('backend.dashboard', compact('data', 'currencySymbol'));
    }
}
