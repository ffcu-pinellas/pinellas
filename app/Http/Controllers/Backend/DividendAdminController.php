<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\DividendLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\DividendService;
use Illuminate\Http\Request;

class DividendAdminController extends Controller
{
    protected DividendService $dividendService;

    public function __construct(DividendService $dividendService)
    {
        $this->middleware('permission:customer-balance-add-or-subtract|officer-balance-manage');
        $this->dividendService = $dividendService;
    }

    /**
     * Display Monthly Dividend Manager dashboard.
     */
    public function index()
    {
        DividendService::autoSyncSchema();

        $globalSettings = $this->dividendService->getGlobalSettings();
        $preview = $this->dividendService->previewBatch();
        $recentLogs = DividendLog::with('user')->latest()->take(30)->get();
        $totalHistoricalPaid = DividendLog::where('status', 'success')->sum('amount');
        $totalBatches = DividendLog::distinct('batch_id')->count('batch_id');

        return view('backend.dividend.index', compact(
            'globalSettings',
            'preview',
            'recentLogs',
            'totalHistoricalPaid',
            'totalBatches'
        ));
    }

    /**
     * Execute batch payout across all or selected eligible members.
     */
    public function processBatch(Request $request)
    {
        $selectedUserIds = $request->input('user_ids');
        if (is_string($selectedUserIds)) {
            $selectedUserIds = array_filter(array_map('trim', explode(',', $selectedUserIds)));
        }

        $result = $this->dividendService->processBatch($selectedUserIds, 'Admin Portal Manual Run');

        if ($result['success']) {
            notify()->success($result['message'], __('Dividends Processed'));
        } else {
            notify()->warning($result['message'], __('Notice'));
        }

        return redirect()->back();
    }

    /**
     * Process individual dividend payout on demand for a single user.
     */
    public function processSingle(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $customApy = $request->filled('apy') ? (float)$request->input('apy') : null;

        $result = $this->dividendService->processSingleUser($user, 'Admin Individual Payout', $customApy);

        if ($result['success']) {
            notify()->success($result['message'], __('Dividend Credited'));
        } else {
            notify()->error($result['message'], __('Error'));
        }

        return redirect()->back();
    }

    /**
     * Update global dividend settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'savings_dividend_apy' => 'required|numeric|min:0|max:99.99',
            'savings_dividend_min_balance' => 'required|numeric|min:0',
            'savings_dividend_description' => 'required|string|max:255',
        ]);

        Setting::updateOrCreate(
            ['key' => 'savings_dividend_enabled', 'section' => 'dividend'],
            ['value' => $request->has('savings_dividend_enabled') ? 1 : 0]
        );

        Setting::updateOrCreate(
            ['key' => 'savings_dividend_apy', 'section' => 'dividend'],
            ['value' => $request->input('savings_dividend_apy')]
        );

        Setting::updateOrCreate(
            ['key' => 'savings_dividend_min_balance', 'section' => 'dividend'],
            ['value' => $request->input('savings_dividend_min_balance')]
        );

        Setting::updateOrCreate(
            ['key' => 'savings_dividend_description', 'section' => 'dividend'],
            ['value' => $request->input('savings_dividend_description')]
        );

        notify()->success(__('Global Dividend settings updated successfully.'), __('Settings Saved'));
        return redirect()->back();
    }
}
