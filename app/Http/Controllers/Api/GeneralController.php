<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Enums\TxnType;
use App\Models\Branch;
use App\Models\Setting;
use App\Models\Currency;
use App\Models\OthersBank;
use App\Models\PageSetting;
use App\Models\WireTransfar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\BankResource;

class GeneralController extends Controller
{
    public function getCountries()
    {
        return response()->json([
            'status' => true,
            'data' => getCountries(),
        ]);
    }

    public function getBranches()
    {
        if (! branch_enabled()) {
            return response()->json([
                'status' => false,
                'message' => 'Branch system is disabled',
                'data' => [],
            ]);
        }
        $branches = Branch::where('status', 1)->get();

        return response()->json([
            'status' => true,
            'data' => $branches->toArray(),
        ]);
    }

    public function getBanks()
    {
        $banks = OthersBank::all();

        return response()->json([
            'status' => true,
            'data' => BankResource::collectionWithDefault($banks, auth()->user()),
        ]);
    }

    public function getCurrencies()
    {
        $multiCurrencyEnabled = setting('multiple_currency', 'permission');
        if (! $multiCurrencyEnabled) {
            return response()->json([
                'status' => false,
                'message' => 'Multiple currency is disabled',
                'data' => [],
            ]);
        }

        $currencies = Currency::all();

        return response()->json([
            'status' => true,
            'data' => $currencies->toArray(),
        ]);
    }

    public function getSettings(Request $request)
    {
        $type = $request->get('key', 'all');
        $settings = Setting::select('name', 'val')->get()->map(function ($setting) {
            return [
                'name' => $setting->name,
                'value' => file_exists(base_path('assets/' . $setting->val)) ? asset($setting->val) : $setting->val,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $type == 'all' ? $settings : collect($settings)->where('name', $type)->value('value'),
        ]);
    }

    public function getLanguages()
    {
        if (! setting('language_switcher')) {
            return response()->json([
                'status' => false,
                'message' => 'Language switcher is disabled',
                'data' => [],
            ]);
        }
        $languages = \App\Models\Language::where('status', 1)->get();

        return response()->json([
            'status' => true,
            'data' => $languages->toArray(),
        ]);
    }

    public function getRegisterFields()
    {
        $registerFields = PageSetting::whereNotIn('key', ['shape_one', 'shape_two', 'shape_three', 'basic_page_background', 'breadcrumb'])->get();

        return response()->json([
            'status' => true,
            'data' => $registerFields,
        ]);
    }

    public function getTransactionTypes()
    {
        $transactionTypes = collect(TxnType::cases())->map(function ($txnType) {
            return [
                'name' => ucwords(str_replace('_', ' ', $txnType->value)),
                'value' => $txnType->value,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $transactionTypes,
        ]);
    }

    public function wireTransferSettings()
    {
        return response()->json([
            'status' => true,
            'data' => WireTransfar::first()->toArray(),
        ]);
    }

    public function getAccounts($account_id)
    {
        $user = User::where('account_number', sanitizeAccountNumber($account_id))->first();

        return response()->json([
            'status' => true,
            'data' => [
                'name' => $user->full_name ?? '',
                'branch_name' => $user->branch?->name ?? '',
            ],
        ]);
    }
}
