<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReconciliationService;
use Illuminate\Http\Request;

class ReconciliationController extends Controller
{
    public function __construct(protected ReconciliationService $reconcileService)
    {
        $this->middleware('permission:site-setting');
    }

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'mismatches');
        $search = trim((string) $request->get('search', ''));

        if ($search !== '') {
            $matchedUsers = User::where('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('account_number', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orderBy('id')
                ->get();

            $audits = [];
            $discrepancyUsers = 0;
            $totalDrift = 0.0;
            foreach ($matchedUsers as $u) {
                $a = $this->reconcileService->auditUser($u);
                if ($a['has_discrepancy']) {
                    $discrepancyUsers++;
                    foreach ($a['wallets'] as $w) {
                        if (!$w['is_match']) {
                            $totalDrift += $w['abs_delta'];
                        }
                    }
                }
                if ($filter !== 'mismatches' || $a['has_discrepancy']) {
                    $audits[] = $a;
                }
            }

            $report = [
                'total_users_scanned' => $matchedUsers->count(),
                'discrepancy_users' => $discrepancyUsers,
                'total_absolute_drift' => round($totalDrift, 2),
                'audits' => $audits,
            ];
        } else {
            $report = $this->reconcileService->auditAll($filter === 'mismatches');
        }

        return view('backend.setting.reconcile.index', compact('report', 'filter', 'search'));
    }

    public function syncBalance(Request $request, int $userId, string $walletKey)
    {
        $res = $this->reconcileService->syncWalletToLedger($userId, $walletKey);
        if (!$res['success']) {
            notify()->error($res['message'], 'Reconcile Error');
            return redirect()->back();
        }

        $cur = setting('currency_symbol', 'global') ?: '$';
        notify()->success(
            "Synced {$res['wallet_label']} for {$res['user']->username}: {$cur}" . number_format($res['old_balance'], 2) . " → {$cur}" . number_format($res['new_balance'], 2),
            'Balance Synced'
        );

        return redirect()->back();
    }

    public function insertBalancing(Request $request, int $userId, string $walletKey)
    {
        $res = $this->reconcileService->insertBalancingEntry($userId, $walletKey, auth('admin')->id());
        if (!$res['success']) {
            notify()->error($res['message'], 'Reconcile Error');
            return redirect()->back();
        }

        $cur = setting('currency_symbol', 'global') ?: '$';
        notify()->success(
            "Inserted {$res['entry_type']} {$cur}" . number_format($res['amount'], 2) . " ({$res['tnx']}) for {$res['user']->username}.",
            'Ledger Balanced'
        );

        return redirect()->back();
    }
}
