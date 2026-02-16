<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Models\DepositMethod;
use App\Models\Transaction;
use App\Traits\ImageUpload;
use App\Traits\NotifyTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Txn;
use Validator;

class DepositController extends Controller
{
    use ImageUpload, NotifyTrait;

    public function deposit($code = 'default')
    {
        if (! setting('user_deposit', 'permission') || ! Auth::user()->deposit_status) {
            notify()->error(__('Deposit currently unavailable'), 'Error');

            return to_route('user.dashboard');
        } elseif (! setting('kyc_deposit') && ! auth()->user()->kyc) {
            notify()->error(__('Please verify your KYC.'), 'Error');

            return to_route('user.dashboard');
        }

        $isStepOne = 'current';
        $isStepTwo = '';
        $gateways = DepositMethod::where('status', 1)->get();
        $wallets = auth()->user()->wallets->load('currency');

        return view('frontend::deposit.now', compact('isStepOne', 'code', 'isStepTwo', 'gateways', 'wallets'));
    }

    public function depositNow(Request $request)
    {
        if (! setting('user_deposit', 'permission') || ! Auth::user()->deposit_status) {
            notify()->error(__('Deposit currently unavailable!'), 'Error');

            return to_route('user.dashboard');
        } elseif (! setting('kyc_deposit') && ! auth()->user()->kyc) {
            notify()->error(__('Please verify your KYC.'), 'Error');

            return to_route('user.dashboard');
        }

        $validator = Validator::make($request->all(), [
            'gateway_code' => 'required',
            'amount' => ['required', 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/'],
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');

            return redirect()->back();
        }

        $input = $request->all();

        $gatewayInfo = DepositMethod::code($input['gateway_code'])->first();
        $amount = $input['amount'];

        if ($gatewayInfo->type != \App\Enums\GatewayType::Manual->value) {
            notify()->error(__('Invalid deposit method!'), 'Error');
            return redirect()->back();
        }

        if ($amount < $gatewayInfo->minimum_deposit || $amount > $gatewayInfo->maximum_deposit) {
            $currencySymbol = setting('currency_symbol', 'global');
            $message = 'Please Deposit the Amount within the range '.$currencySymbol.$gatewayInfo->minimum_deposit.' to '.$currencySymbol.$gatewayInfo->maximum_deposit;
            notify()->error($message, 'Error');

            return redirect()->back();
        }

        $charge = $gatewayInfo->charge_type == 'percentage' ? (($gatewayInfo->charge / 100) * $amount) : $gatewayInfo->charge;
        $finalAmount = (float) $amount + (float) $charge;
        $payAmount = $finalAmount * $gatewayInfo->rate;
        $depositType = TxnType::ManualDeposit;

        if (isset($input['manual_data'])) {
            $manualData = $input['manual_data'];
            foreach ($manualData as $key => $value) {
                if (is_file($value)) {
                    $manualData[$key] = self::imageUploadTrait($value);
                }
            }
        }

        // Wallet type
        $walletType = $request->get('wallet_type', 'default');

        $txnInfo = Txn::new($input['amount'], $charge, $finalAmount, $gatewayInfo->gateway_code, 'Deposit With '.$gatewayInfo->name, $depositType, TxnStatus::Pending, $gatewayInfo->currency, $payAmount, auth()->id(), null, 'User', $manualData ?? [], $walletType);

        $symbol = setting('currency_symbol', 'global');
        $notify = [
            'card-header' => "Pending Your Deposit Process",
            'title' => "$symbol $txnInfo->amount Deposit Pending",
            'p' => "The amount has been added as pending into your account,",
            'strong' => 'Transaction ID: '.$txnInfo->tnx,
            'action' => route('user.deposit.amount'),
            'a' => 'Deposit again',
        ];

        $isStepOne = 'current';
        $isStepTwo = 'current';

        return view('frontend::deposit.success', compact('isStepOne', 'isStepTwo', 'notify'));
    }

    public function depositSuccess()
    {
        return view('frontend::deposit.success');
    }

    public function gateway($code)
    {
        $gateway = DepositMethod::code($code)->first();

        return response()->json($gateway);
    }

    public function getGateways($currency)
    {
        $gateways = DepositMethod::where('status', 1)->get();
        $options = '<option value="" disabled selected>--' . __('Select Gateway') . '--</option>';
        foreach ($gateways as $gateway) {
            $options .= '<option data-logo="' . asset($gateway->logo) . '" value="' . $gateway->gateway_code . '">' . $gateway->name . '</option>';
        }

        return response()->json([
            'options' => $options,
        ]);
    }

    public function depositLog()
    {
        $from_date = trim(@explode('-', request('daterange'))[0]);
        $to_date = trim(@explode('-', request('daterange'))[1]);

        $deposits = Transaction::where('user_id', auth()->user()->id)
            ->search(request('trx'))
            ->whereIn('type', [TxnType::Deposit, TxnType::ManualDeposit])
            ->when(request('daterange'), function ($query) use ($from_date, $to_date) {
                $query->whereDate('created_at', '>=', Carbon::parse($from_date)->format('Y-m-d'));
                $query->whereDate('created_at', '<=', Carbon::parse($to_date)->format('Y-m-d'));
            })->latest()->paginate(request('limit', 15))->withQueryString();

        return view('frontend::deposit.log', compact('deposits'));
    }
}
