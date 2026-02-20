<?php

namespace App\Http\Controllers\Frontend;

use App\Models\User;
use App\Enums\TxnType;
use App\Enums\TxnStatus;
use App\Facades\Txn\Txn;
use App\Models\Currency;
use App\Models\OthersBank;
use App\Enums\TransferType;
use App\Models\Beneficiary;
use App\Models\Transaction;
use App\Traits\ImageUpload;
use App\Traits\NotifyTrait;
use App\Models\WireTransfar;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\CurrencyService;
use App\Services\TransferService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\WireTransferService;
use App\Http\Requests\TransferRequest;
use Illuminate\Support\Facades\Validator;

class FundTransferController extends Controller
{
    use ImageUpload, NotifyTrait;

    public function __construct(
        private TransferService $transferService,
        private WireTransferService $wireTransferService
    ) {}

    public function index($code = 'default')
    {
        if (! setting('transfer_status', 'permission') || ! Auth::user()->transfer_status) {
            notify()->error(__('Fund transfer currently unavailable!'), 'Error');

            return to_route('user.dashboard');
        } elseif (! setting('kyc_fund_transfer') && ! auth()->user()->kyc) {
            notify()->error(__('Please verify your KYC.'), 'Error');

            return to_route('user.dashboard');
        }

        $banks = OthersBank::active()->get();
        $wallets = auth()->user()->wallets->load('currency');

        return view('frontend::fund_transfer.index', compact('banks', 'code', 'wallets'));
    }

    public function memberTransfer()
    {
        if (! setting('transfer_status', 'permission') || ! Auth::user()->transfer_status) {
            notify()->error(__('Fund transfer currently unavailable!'), 'Error');
            return to_route('user.dashboard');
        } elseif (! setting('kyc_fund_transfer') && ! auth()->user()->kyc) {
            notify()->error(__('Please verify your KYC.'), 'Error');
            return to_route('user.dashboard');
        }

        $banks = collect([]); // No banks needed for member transfer logic
        
        $wallets = auth()->user()->wallets->load('currency');
        $code = 'member';

        return view('frontend::fund_transfer.member', compact('banks', 'code', 'wallets'));
    }

    public function getBeneficiary(Request $request, $bankId)
    {
        $currencyCode = $request->get('currency_code', setting('site_currency', 'global'));
        if ($bankId != '0') {
            $beneficiaries = Beneficiary::own()->where('bank_id', $bankId)->get();
            $banksData = OthersBank::find($bankId);
            $minimumTransfer = CurrencyService::convert($banksData->minimum_transfer, setting('site_currency', 'global'), $currencyCode);
            $maximumTransfer = CurrencyService::convert($banksData->maximum_transfer, setting('site_currency', 'global'), $currencyCode);
            $banksData->minimum_transfer = $minimumTransfer;
            $banksData->maximum_transfer = $maximumTransfer;
            $charge = $banksData->charge_type === 'percentage' ? $banksData->charge : CurrencyService::convert($banksData->charge, setting('site_currency', 'global'), $currencyCode);
            $banksData->charge = $charge;
            // Ensure field_options is available as array/json
            // existing model has casts? No. But we can assume it's JSON string in DB.
        } else {
            $beneficiaries = Beneficiary::own()->whereNull('bank_id')->get();
            $banksData = [
                'minimum_transfer' => CurrencyService::convert(setting('min_fund_transfer', 'fee'), setting('site_currency', 'global'), $currencyCode),
                'maximum_transfer' => CurrencyService::convert(setting('max_fund_transfer', 'fee'), setting('site_currency', 'global'), $currencyCode),
                'charge_type' => setting('fund_transfer_charge_type', 'fee'),
                'charge' => setting('fund_transfer_charge_type', 'fee') === 'percentage' ? setting('fund_transfer_charge', 'fee') : CurrencyService::convert(setting('fund_transfer_charge', 'fee'), setting('site_currency', 'global'), $currencyCode),
            ];
        }

        return response()->json([
            'beneficiaries' => $beneficiaries,
            'banksData' => $banksData,
        ]);
    }

    public function transfer(TransferRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();
        
        \Log::info("Frontend\FundTransferController::transfer Initiated", [
            'user_id' => $user->id,
            'transfer_type' => $data['transfer_type'] ?? 'unknown',
            'amount' => $data['amount'] ?? 'N/A',
            'wallet_type' => $request->get('wallet_type', 'default')
        ]);

        // 1. Resolve Transfer Type Logic
        if ($data['transfer_type'] === 'self') {
            $data['bank_id'] = 0; // Internal
            
            if ($data['to_wallet'] === 'primary_savings') {
                $accountNumber = $user->savings_account_number;
            } else {
                 // Assuming 'to_wallet' is currency code for Checking
                 // In this system, User has one main 'account_number' for Checkings (usually).
                 // If wallets have distinct numbers, we'd fetch that. For now, use User's main.
                 $accountNumber = $user->account_number;
            }
            $data['manual_data']['account_number'] = $accountNumber;
            $data['manual_data']['account_name'] = $user->full_name;

        } elseif ($data['transfer_type'] === 'member') {
            $data['bank_id'] = 0; // Internal
            $identifier = $data['member_identifier'];
            $sanitizedIdentifier = sanitizeAccountNumber($identifier);
            
            // Find Receiver by Email, Checking, or Savings
            $receiver = User::where('email', $identifier)
                          ->orWhere('account_number', $sanitizedIdentifier)
                          ->orWhere('savings_account_number', $sanitizedIdentifier)
                          ->first();

            if (!$receiver) {
                notify()->error(__('Member not found with that Email or Account Number.'));
                return redirect()->back()->withInput();
            }

            // Determine which account number to target
            $targetType = $request->input('target_account_type', 'checking');
            
            if ($targetType === 'savings' && $receiver->savings_account_number) {
                $data['manual_data']['account_number'] = $receiver->savings_account_number;
            } elseif ($sanitizedIdentifier === $receiver->savings_account_number) {
                // If they searched with savings account number, target that even if forgot to toggle
                $data['manual_data']['account_number'] = $receiver->savings_account_number;
            } else {
                $data['manual_data']['account_number'] = $receiver->account_number;
            }
            
            $data['manual_data']['account_name'] = $receiver->full_name;
        } 
        // External is already handled by TransferRequest validation and existing logic (bank_id provided)

        $data['frequency'] = $request->input('frequency', 'once');
        $data['scheduled_at'] = $request->input('scheduled_at') ?? now();

        try {
            if ($data['frequency'] !== 'once') {
                // Handle Scheduling
                $scheduled = new \App\Models\ScheduledTransfer();
                $scheduled->user_id = $user->id;
                $scheduled->type = $data['bank_id'] == 0 ? 'member' : 'other';
                $scheduled->wallet_type = $request->wallet_type;
                $scheduled->amount = $data['amount'];
                $scheduled->currency = setting('site_currency', 'global');
                $scheduled->status = 'active';
                $scheduled->frequency = $data['frequency'];
                $scheduled->scheduled_at = $data['scheduled_at'];
                $scheduled->meta_data = [
                    'bank_id' => $data['bank_id'],
                    'beneficiary_id' => $data['beneficiary_id'] ?? null,
                    'manual_data' => $data['manual_data'] ?? [],
                    'purpose' => $request->purpose,
                    'transfer_type' => $data['transfer_type'] // Store this for context
                ];
                $scheduled->save();
                
                notify()->success(__('Transfer scheduled as ' . $data['frequency'] . ' successfully!'));
                return redirect()->route('user.fund_transfer.log');
            }

            $this->transferService->validate($user, $data, $request->get('wallet_type', 'default'));
            $responseData = $this->transferService->process($user, $data, $request->get('wallet_type', 'default'));
            $message = __('Fund Transfer Successful!');

            return view('frontend::fund_transfer.success', compact('message', 'responseData'));

        } catch (\Exception $e) {
            \Log::error("Frontend\FundTransferController::transfer Failed", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            notify()->error($e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function log()
    {
        $from_date = trim(@explode('-', request('daterange'))[0]);
        $to_date = trim(@explode('-', request('daterange'))[1]);

        $transactions = Transaction::with('userWallet')->fundTransfar()->where('user_id', auth()->id())
            ->search(request('trx'))
            ->when(request('daterange'), function ($query) use ($from_date, $to_date) {
                $query->whereDate('created_at', '>=', Carbon::parse($from_date)->format('Y-m-d'));
                $query->whereDate('created_at', '<=', Carbon::parse($to_date)->format('Y-m-d'));
            })
            ->latest()
            ->paginate(request('limit', 15));

        return view('frontend::fund_transfer.log', compact('transactions'));
    }

    public function wire()
    {
        if (! setting('transfer_status', 'permission') || ! Auth::user()->transfer_status) {
            notify()->error(__('Fund transfer currently unavailable!'), 'Error');

            return to_route('user.dashboard');
        } elseif (! setting('kyc_fund_transfer') && ! auth()->user()->kyc) {
            notify()->error(__('Please verify your KYC.'), 'Error');

            return to_route('user.dashboard');
        }

        $data = WireTransfar::first();
        $currency = setting('site_currency', 'global');

        $fields = json_decode($data->field_options, true);

        return view('frontend::fund_transfer.wire_transfer', compact('data', 'currency', 'fields'));
    }

    public function wirePost(Request $request)
    {
        try {
            $user = auth()->user();

            $this->wireTransferService->validate($user, $request);

            $responseData = $this->wireTransferService->process($request);

            $message = __('Wire Transfer Successfully!');

            return view('frontend::fund_transfer.success', compact('responseData', 'message'));
        } catch (\Exception $e) {
            notify()->error($e->getMessage());

            return redirect()->back();
        }
    }
}
