<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bank_id' => 'required_if:transfer_type,external',
            'beneficiary_id' => 'nullable',
            'transfer_type' => 'required|in:self,member,external',
            'wallet_type' => 'required',
            'amount' => ['required', 'numeric', 'min:0.01'],
            'frequency' => 'required|in:once,daily,weekly,monthly',
            
            // Self Transfer
            'to_wallet' => 'required_if:transfer_type,self',
            
            // Member Transfer
            'email' => 'nullable|email', // Optional identifier
            'member_identifier' => 'required_if:transfer_type,member',
            'target_account_type' => 'nullable|in:checking,savings',

            // External
            'manual_data.account_name' => 'required_if:transfer_type,external',
            'manual_data.account_number' => 'required_if:transfer_type,external',
            'manual_data.routing_number' => 'required_if:transfer_type,external',
            
            'purpose' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'manual_data.*.required_if' => __('Select beneficiary or fill up account name, number, branch name.'),
        ];
    }
}
