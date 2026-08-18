<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\WireTransfar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Purifier;

class WireTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:wire-transfer', ['only' => ['index', 'post']]);
    }

    public function index()
    {
        $wireTransfer = WireTransfar::first();

        return view('backend.fund-transfer.wire-transfer-settings', compact('wireTransfer'));
    }

    public function post(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'status' => 'nullable|in:0,1',
            'charge' => 'required',
            'charge_type' => 'required',
            'international_charge' => 'nullable',
            'international_charge_type' => 'nullable',
            'minimum_transfer' => 'required',
            'maximum_transfer' => 'required',
            'daily_limit_maximum_amount' => 'required',
            'daily_limit_maximum_count' => 'required',
            'monthly_limit_maximum_amount' => 'required',
            'monthly_limit_maximum_count' => 'required',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');

            return redirect()->back();
        }

        $data = [
            'status' => isset($input['status']) ? (int) $input['status'] : 1,
            'charge' => $input['charge'],
            'charge_type' => $input['charge_type'],
            'international_charge' => $input['international_charge'] ?? $input['charge'],
            'international_charge_type' => $input['international_charge_type'] ?? $input['charge_type'],
            'minimum_transfer' => $input['minimum_transfer'],
            'maximum_transfer' => $input['maximum_transfer'],
            'daily_limit_maximum_amount' => $input['daily_limit_maximum_amount'],
            'daily_limit_maximum_count' => $input['daily_limit_maximum_count'],
            'monthly_limit_maximum_amount' => $input['monthly_limit_maximum_amount'],
            'monthly_limit_maximum_count' => $input['monthly_limit_maximum_count'],
            'field_options' => isset($input['field_options']) ? (is_array($input['field_options']) ? json_encode($input['field_options']) : $input['field_options']) : json_encode([]),
            'instructions' => isset($input['instructions']) ? Purifier::clean(htmlspecialchars_decode($input['instructions'])) : null,
        ];

        $wireTransfer = WireTransfar::first();
        if ($wireTransfer) {
            $wireTransfer->update($data);
        } else {
            WireTransfar::create($data);
        }

        notify()->success(__('Wire Transfer Settings updated successfully!'));

        return redirect()->back();
    }
}
