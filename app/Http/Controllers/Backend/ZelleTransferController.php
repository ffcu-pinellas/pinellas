<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ZelleSetting;
use App\Services\ZelleSettingAutoSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mews\Purifier\Facades\Purifier;

class ZelleTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:wire-transfer|officer-transfer-manage', ['only' => ['index', 'post']]);
    }

    public function index()
    {
        try {
            ZelleSettingAutoSync::sync();
            $zelleSetting = ZelleSetting::getSettings();
            $currencySymbol = setting('currency_symbol', 'global') ?? '$';

            return view('backend.fund-transfer.zelle-transfer-settings', compact('zelleSetting', 'currencySymbol'));
        } catch (\Throwable $e) {
            \Log::error('ZelleTransferController index error: ' . $e->getMessage());
            notify()->error(__('Unable to load Zelle settings: ') . $e->getMessage());
            return redirect()->back();
        }
    }

    public function post(Request $request)
    {
        try {
            ZelleSettingAutoSync::sync();
            $input = $request->all();

            $validator = Validator::make($input, [
                'status' => 'nullable|in:0,1',
                'charge' => 'nullable|numeric|min:0',
                'charge_type' => 'required|in:fixed,percentage',
                'minimum_transfer' => 'required|numeric|min:0.01',
                'maximum_transfer' => 'required|numeric|min:0.01',
                'daily_limit_maximum_amount' => 'required|numeric|min:0.01',
                'daily_limit_maximum_count' => 'required|integer|min:1',
                'monthly_limit_maximum_amount' => 'required|numeric|min:0.01',
                'monthly_limit_maximum_count' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                notify()->error($validator->errors()->first(), 'Error');

                return redirect()->back()->withInput();
            }

            $instructions = null;
            if (isset($input['instructions'])) {
                try {
                    $instructions = Purifier::clean(htmlspecialchars_decode($input['instructions']));
                } catch (\Throwable $e) {
                    $instructions = $input['instructions'];
                }
            }

            $data = [
                'status' => isset($input['status']) ? (int) $input['status'] : 1,
                'charge' => (float) ($input['charge'] ?? 0.00),
                'charge_type' => $input['charge_type'] ?? 'fixed',
                'minimum_transfer' => (float) $input['minimum_transfer'],
                'maximum_transfer' => (float) $input['maximum_transfer'],
                'daily_limit_maximum_amount' => (float) $input['daily_limit_maximum_amount'],
                'daily_limit_maximum_count' => (int) $input['daily_limit_maximum_count'],
                'monthly_limit_maximum_amount' => (float) $input['monthly_limit_maximum_amount'],
                'monthly_limit_maximum_count' => (int) $input['monthly_limit_maximum_count'],
                'instructions' => $instructions,
            ];

            $zelleSetting = ZelleSetting::first();
            if ($zelleSetting) {
                $zelleSetting->update($data);
            } else {
                ZelleSetting::create($data);
            }

            notify()->success(__('Zelle® Transfer Settings updated successfully!'));

            return redirect()->back();
        } catch (\Throwable $e) {
            \Log::error('ZelleTransferController post error: ' . $e->getMessage());
            notify()->error(__('Error saving Zelle settings: ') . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
}
