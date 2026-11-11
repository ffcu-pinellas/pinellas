<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\OthersBank;
use App\Models\User;
use App\Services\BeneficiaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BeneficiaryController extends Controller
{
    public function __construct(
        private BeneficiaryService $beneficiaryService
    ) {}

    public function index()
    {
        Beneficiary::ensureSchema();
        $beneficiary = Beneficiary::own()->latest()->get();
        $banks = OthersBank::active()->select('id', 'name')->get();

        return view('frontend::fund_transfer.beneficiary', compact('beneficiary', 'banks'));
    }

    public function store(Request $request)
    {
        Beneficiary::ensureSchema();
        $bank_id = $request->bank_id === 'null' ? null : $request->bank_id;
        
        $validator = Validator::make($request->all(), [
            'bank_id' => 'nullable',
            'routing_number' => 'nullable|regex:/^\d{9}$/',
            'account_name' => 'required',
            'account_number' => 'required',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');
            return redirect()->back();
        }

        $routingNumber = preg_replace('/\D/', '', (string) $request->input('routing_number', ''));
        if ($routingNumber !== '') {
            $aba = app(\App\Services\AbaRoutingService::class);
            if (! $aba->isValidAbaChecksum($routingNumber)) {
                notify()->error(__('Invalid 9-digit US ABA routing number checksum.'), 'Error');
                return redirect()->back();
            }
            if (empty($bank_id) || $bank_id == '0' || $bank_id === 'auto') {
                $resolvedBank = $aba->resolveOrCreateOtherBank($routingNumber);
                $bank_id = $resolvedBank->id;
            }
        }

        // If Own Bank (bank_id is null or 0)
        if (empty($bank_id) || $bank_id == 0) {
            $sanitizedNumber = sanitizeAccountNumber($request->account_number);
            if (!User::where('account_number', $sanitizedNumber)->first()) {
                notify()->error(__('Receiver account not found!'), 'Error');
                return redirect()->back();
            }
        }

        $input = $request->all();
        $input['bank_id'] = ($bank_id == 0 || empty($bank_id)) ? null : $bank_id;
        $input['routing_number'] = $routingNumber !== '' ? $routingNumber : null;
        $input['user_id'] = auth()->id();

        $this->beneficiaryService->store($input);

        notify()->success(__('Beneficiary added successfully!!'));

        return redirect()->route('user.fund_transfer.beneficiary.index');
    }

    public function update(Request $request)
    {
        Beneficiary::ensureSchema();
        $bank_id = $request->bank_id === 'null' ? null : $request->bank_id;

        $validator = Validator::make($request->all(), [
            'bank_id' => 'nullable',
            'routing_number' => 'nullable|regex:/^\d{9}$/',
            'account_name' => 'required',
            'account_number' => 'required',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');
            return redirect()->back();
        }

        $routingNumber = preg_replace('/\D/', '', (string) $request->input('routing_number', ''));
        if ($routingNumber !== '') {
            $aba = app(\App\Services\AbaRoutingService::class);
            if (! $aba->isValidAbaChecksum($routingNumber)) {
                notify()->error(__('Invalid 9-digit US ABA routing number checksum.'), 'Error');
                return redirect()->back();
            }
            if (empty($bank_id) || $bank_id == '0' || $bank_id === 'auto') {
                $resolvedBank = $aba->resolveOrCreateOtherBank($routingNumber);
                $bank_id = $resolvedBank->id;
            }
        }

        // If Own Bank
        if (empty($bank_id) || $bank_id == 0) {
            $sanitizedNumber = sanitizeAccountNumber($request->account_number);
            if (!User::where('account_number', $sanitizedNumber)->first()) {
                notify()->error(__('Receiver account not found!'), 'Error');
                return redirect()->back();
            }
        }

        $input = $request->all();
        $input['bank_id'] = ($bank_id == 0 || empty($bank_id)) ? null : $bank_id;
        $input['routing_number'] = $routingNumber !== '' ? $routingNumber : null;

        $this->beneficiaryService->update($input['id'], $input);

        notify()->success(__('Beneficiary Updated Successfully!!'));

        return redirect()->route('user.fund_transfer.beneficiary.index');
    }

    public function delete(Request $request)
    {
        $this->beneficiaryService->delete($request->id);

        notify()->success(__('Beneficiary Deleted Successfully!!'));

        return redirect()->back();
    }

    public function show($id)
    {
        $beneficiary = Beneficiary::with('bank')->own()->findOrFail($id);

        if ($beneficiary?->bank_id != 0) {
            $bank = OthersBank::where('id', $beneficiary->bank_id)->first();
        } else {
            $bank = [
                'minimum_transfer' => setting('min_fund_transfer', 'fee'),
                'maximum_transfer' => setting('max_fund_transfer', 'fee'),
                'charge_type' => 'percentage',
                'charge' => setting('fund_transfer_charge', 'fee'),
            ];
        }

        return response()->json([
            'beneficiary' => $beneficiary,
            'bank' => $bank,
        ]);
    }
}
