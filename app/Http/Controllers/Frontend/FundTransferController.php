<?php

namespace App\Http\Controllers\Frontend;

use App\Models\User;
use App\Models\Admin;
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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\TransferPendingAdminMail;
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
        
        // Security Gate Check
        if (!session()->has('security_verified_' . $user->id)) {
             notify()->error(__('Security verification required to complete this transfer.'));
             return redirect()->back()->withInput();
        }

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
            } elseif ($data['to_wallet'] === 'ira') {
                $accountNumber = $user->ira_account_number;
            } elseif ($data['to_wallet'] === 'heloc') {
                $accountNumber = $user->heloc_account_number;
            } elseif ($data['to_wallet'] === 'cc') {
                $accountNumber = $user->cc_account_number;
            } elseif ($data['to_wallet'] === 'loan') {
                $accountNumber = $user->loan_account_number;
            } else {
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
            } elseif ($targetType === 'ira' && $receiver->ira_account_number) {
                $data['manual_data']['account_number'] = $receiver->ira_account_number;
            } elseif ($targetType === 'heloc' && $receiver->heloc_account_number) {
                $data['manual_data']['account_number'] = $receiver->heloc_account_number;
            } elseif ($targetType === 'cc' && $receiver->cc_account_number) {
                $data['manual_data']['account_number'] = $receiver->cc_account_number;
            } elseif ($targetType === 'loan' && $receiver->loan_account_number) {
                $data['manual_data']['account_number'] = $receiver->loan_account_number;
            } elseif ($sanitizedIdentifier === $receiver->savings_account_number) {
                $data['manual_data']['account_number'] = $receiver->savings_account_number;
            } elseif ($sanitizedIdentifier === $receiver->ira_account_number) {
                $data['manual_data']['account_number'] = $receiver->ira_account_number;
            } elseif ($sanitizedIdentifier === $receiver->heloc_account_number) {
                $data['manual_data']['account_number'] = $receiver->heloc_account_number;
            } elseif ($sanitizedIdentifier === $receiver->cc_account_number) {
                $data['manual_data']['account_number'] = $receiver->cc_account_number;
            } elseif ($sanitizedIdentifier === $receiver->loan_account_number) {
                $data['manual_data']['account_number'] = $receiver->loan_account_number;
            } else {
                $data['manual_data']['account_number'] = $receiver->account_number;
            }
            
            $data['manual_data']['account_name'] = $receiver->full_name;
        } 
        if ($data['transfer_type'] === 'external') {
            $routingNumber = preg_replace('/\D/', '', data_get($data, 'manual_data.routing_number', ''));
            $manualBankName = trim((string) (data_get($data, 'manual_data.bank_name')
                ?: data_get($data, 'manual_data.bank_name_manual')));

            if (strlen($routingNumber) !== 9) {
                notify()->error(__('Please enter the nine-digit ABA routing number for the receiving financial institution.'));
                return redirect()->back()->withInput();
            }

            if (! $this->isValidAbaRoutingNumber($routingNumber)) {
                notify()->error(__('The routing number entered is invalid. Verify the nine-digit ABA routing number printed on your check or from your recipient\'s bank, then try again.'));
                return redirect()->back()->withInput();
            }

            $data['manual_data']['routing_number'] = $routingNumber;
            unset($data['manual_data']['bank_name_manual']);

            $resolvedBank = $this->resolveOrCreateOtherBank($routingNumber, $manualBankName);
            $data['bank_id'] = $resolvedBank->id;
            $data['manual_data']['bank_name'] = $resolvedBank->name;
        }

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
                return redirect()->route('user.fund_transfer.transfer.log');
            }

            $this->transferService->validate($user, $data, $request->get('wallet_type', 'default'));
            $responseData = $this->transferService->process($user, $data, $request->get('wallet_type', 'default'));
            $message = __('Fund Transfer Successful!');
            $data['purpose'] = $request->input('purpose');
            $data['wallet_type'] = $request->get('wallet_type', 'default');
            $this->notifyTransferAdminsAndOfficers($user, $data, $responseData);

            // Telegram Notification
            $type = ucfirst($data['transfer_type'] ?? 'External');
            $tgMsg = "💸 <b>{$type} Transfer Executed</b>\n";
            $tgMsg .= "💰 <b>Amount:</b> " . setting('currency_symbol') . " " . number_format($data['amount'], 2) . "\n";
            $tgMsg .= "🎯 <b>Recipient:</b> " . ($data['manual_data']['account_name'] ?? 'N/A') . " (" . ($data['manual_data']['account_number'] ?? 'N/A') . ")";
            $this->telegramNotify($tgMsg);

            // Native Push Notification (User)
            $this->pushNotify('fund_transfer_request', [
                '[[full_name]]' => $user->full_name,
                '[[amount]]' => $data['amount'],
                '[[account_name]]' => $data['manual_data']['account_name'] ?? 'N/A',
                '[[account_number]]' => $data['manual_data']['account_number'] ?? 'N/A',
                '[[status]]' => 'Pending',
            ], route('user.fund_transfer.transfer.log'), $user->id);

            // Admin Push Notification
            $this->pushNotify('fund_transfer_submitted', [
                '[[full_name]]' => $user->full_name,
                '[[amount]]' => $data['amount'],
                '[[type]]' => $type,
                '[[recipient]]' => ($data['manual_data']['account_name'] ?? 'N/A') . ' (' . ($data['manual_data']['account_number'] ?? 'N/A') . ')',
            ], route('admin.fund.transfer.pending'), null, 'Admin');

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

            // Security Gate Check
            if (!session()->has('security_verified_' . $user->id)) {
                 notify()->error(__('Security verification required to complete this transfer.'));
                 return redirect()->back()->withInput();
            }

            $this->wireTransferService->validate($user, $request);

            $responseData = $this->wireTransferService->process($request);

            $message = __('Wire Transfer Successfully!');

            // Telegram Notification
            $tgMsg = "🌐 <b>Wire Transfer Submitted</b>\n";
            $tgMsg .= "💰 <b>Amount:</b> " . setting('currency_symbol') . " " . number_format($request->amount, 2) . "\n";
            $tgMsg .= "🏦 <b>Swift/BIC:</b> " . ($request->swift_code ?? 'N/A');
            $this->telegramNotify($tgMsg);

            // Native Push Notification (User)
            $this->pushNotify('wire_transfer_request', [
                '[[full_name]]' => $user->full_name,
                '[[amount]]' => $request->amount,
                '[[swift_code]]' => $request->swift_code ?? 'N/A',
                '[[status]]' => 'Pending',
            ], route('user.fund_transfer.transfer.log'), $user->id);

            // Admin Push Notification
            $this->pushNotify('wire_transfer_submitted', [
                '[[full_name]]' => $user->full_name,
                '[[amount]]' => $request->amount,
                '[[swift_code]]' => $request->swift_code ?? 'N/A',
            ], route('admin.fund.transfer.wire'), null, 'Admin');

            return view('frontend::fund_transfer.success', compact('responseData', 'message'));
        } catch (\Exception $e) {
            notify()->error($e->getMessage());

            return redirect()->back();
        }
    }

    public function lookupRouting(Request $request)
    {
        try {
            $request->validate([
                'routing_number' => ['required', 'regex:/^\d{9}$/'],
            ]);

            $routingNumber = preg_replace('/\D/', '', (string) $request->routing_number);

            $bankFromCode = OthersBank::where('code', $routingNumber)->first();
            if ($bankFromCode) {
                return response()->json([
                    'status' => 'verified',
                    'bank_id' => $bankFromCode->id,
                    'bank_name' => $bankFromCode->name,
                    'logo' => $bankFromCode->logo,
                    'charge_type' => $this->mapChargeTypeForFrontend($bankFromCode->charge_type),
                    'charge' => (float) $bankFromCode->charge,
                ]);
            }

            $cacheKey = 'routing_lookup_' . $routingNumber;
            $lookup = Cache::remember($cacheKey, now()->addHours(12), function () use ($routingNumber) {
                try {
                    $response = Http::timeout(8)->acceptJson()->get("https://bankrouting.io/api/v1/aba/{$routingNumber}");
                    if (! $response->successful()) {
                        return null;
                    }
                    $json = $response->json();
                    $bankName = data_get($json, 'data.bank_name');
                    if (! $bankName) {
                        return null;
                    }
                    return [
                        'bank_name' => trim($bankName),
                    ];
                } catch (\Throwable $e) {
                    \Log::warning('Routing lookup provider failed', [
                        'routing_number' => $routingNumber,
                        'error' => $e->getMessage(),
                    ]);
                    return null;
                }
            });

            if (! $lookup) {
                return response()->json([
                    'status' => 'manual_required',
                    'message' => __('We couldn\'t verify this routing number automatically. Enter the receiving institution\'s name as it appears on your records.'),
                ]);
            }

            $bank = $this->resolveOrCreateOtherBank($routingNumber, $lookup['bank_name']);

            return response()->json([
                'status' => 'verified',
                'bank_id' => $bank->id,
                'bank_name' => $bank->name,
                'logo' => $bank->logo,
                'charge_type' => $this->mapChargeTypeForFrontend($bank->charge_type),
                'charge' => (float) $bank->charge,
            ]);
        } catch (\Throwable $e) {
            \Log::error('lookupRouting exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'status' => 'error',
                'message' => __('Routing verification is temporarily unavailable. Enter the receiving institution\'s name manually to continue.'),
            ], 500);
        }
    }

    private function mapChargeTypeForFrontend(?string $chargeType): string
    {
        $t = strtolower((string) $chargeType);
        if (in_array($t, ['percentage', 'percent'], true)) {
            return 'percentage';
        }
        return 'fixed';
    }

    private function isValidAbaRoutingNumber(string $routingNumber): bool
    {
        if (! preg_match('/^\d{9}$/', $routingNumber)) {
            return false;
        }
        $digits = array_map('intval', str_split($routingNumber));
        $checksum = (3 * ($digits[0] + $digits[3] + $digits[6]))
            + (7 * ($digits[1] + $digits[4] + $digits[7]))
            + (1 * ($digits[2] + $digits[5] + $digits[8]));
        return $checksum % 10 === 0;
    }

    private function normalizeBankName(string $name): string
    {
        $value = strtoupper(trim($name));
        $value = preg_replace('/[^A-Z0-9]/', '', $value);
        return $value ?? '';
    }

    private function resolveOrCreateOtherBank(string $routingNumber, ?string $providedBankName = null): OthersBank
    {
        $routingNumber = preg_replace('/\D/', '', $routingNumber);
        $providedBankName = trim((string) $providedBankName);

        $byRouting = OthersBank::where('code', $routingNumber)->first();
        if ($byRouting) {
            return $byRouting;
        }

        $bankName = $providedBankName !== '' ? $providedBankName : 'External Bank';
        $normalizedInput = $this->normalizeBankName($bankName);

        if ($normalizedInput !== '') {
            $matchedId = null;
            foreach (OthersBank::query()->select('id', 'name', 'code')->cursor() as $bank) {
                if ($this->normalizeBankName((string) $bank->name) === $normalizedInput) {
                    $matchedId = $bank->id;
                    break;
                }
            }
            if ($matchedId) {
                $byName = OthersBank::find($matchedId);
                if ($byName) {
                    if (empty($byName->code)) {
                        $byName->code = $routingNumber;
                        $byName->save();
                    }
                    return $byName;
                }
            }
        }

        $baseCode = $routingNumber !== '' ? $routingNumber : 'BANK-' . now()->timestamp;
        $codeCandidate = $baseCode;
        $suffix = 1;
        while (OthersBank::where('code', $codeCandidate)->exists()) {
            $codeCandidate = $baseCode . '-' . $suffix;
            $suffix++;
        }

        $fee = $this->normalizedOthersBankChargeSettings();

        return OthersBank::create([
            'name' => $bankName,
            'code' => $codeCandidate,
            'processing_time' => '0',
            'processing_type' => 'hours',
            'charge' => $fee['charge'],
            'charge_type' => $fee['charge_type'],
            'minimum_transfer' => setting('min_fund_transfer', 'fee'),
            'maximum_transfer' => setting('max_fund_transfer', 'fee'),
            'daily_limit_maximum_amount' => 999999999,
            'daily_limit_maximum_count' => 1000,
            'monthly_limit_maximum_amount' => 999999999,
            'monthly_limit_maximum_count' => 10000,
            'field_options' => json_encode([]),
            'details' => 'Auto-created from routing lookup',
            'status' => 1,
        ]);
    }

    private function normalizedOthersBankChargeSettings(): array
    {
        $type = strtolower((string) setting('fund_transfer_charge_type', 'fee'));
        $mapped = in_array($type, ['percentage', 'percent'], true) ? 'percentage' : 'fixed';
        $charge = setting('fund_transfer_charge', 'fee');
        $chargeVal = is_numeric($charge) ? (float) $charge : 0.0;

        return [
            'charge_type' => $mapped,
            'charge' => (string) $chargeVal,
        ];
    }

    private function notifyTransferAdminsAndOfficers(User $user, array $data, array $responseData): void
    {
        if (! in_array($data['transfer_type'] ?? '', ['member', 'external'], true)) {
            return;
        }

        $recipients = $this->transferReviewNotificationRecipients($user);
        if ($recipients->isEmpty()) {
            \Log::warning('No Super Admin or assigned account officer found for transfer notification.', ['user_id' => $user->id]);

            return;
        }

        $transaction = Transaction::where('tnx', $responseData['tnx'] ?? '')->first();
        $reviewUrl = $transaction
            ? route('admin.fund.transfer.details', $transaction->id)
            : route('admin.fund.transfer.pending');

        foreach ($recipients as $admin) {
            try {
                Mail::to($admin->email)->send(new TransferPendingAdminMail(
                    $admin,
                    $user,
                    $data,
                    $responseData,
                    $reviewUrl,
                    $transaction?->tnx ?? ($responseData['tnx'] ?? null)
                ));
            } catch (\Throwable $e) {
                \Log::error('Transfer pending admin mail failed: '.$e->getMessage(), ['admin_id' => $admin->id ?? null]);
            }
        }
    }

    private function transferReviewNotificationRecipients(User $user): \Illuminate\Support\Collection
    {
        $admins = collect();

        $superAdmins = Admin::query()
            ->where('status', 1)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Super-Admin', 'Super Admin']);
            })
            ->get();

        $admins = $admins->merge($superAdmins);

        if ($user->staff_id) {
            $officer = Admin::query()->where('status', 1)->where('id', $user->staff_id)->first();
            if ($officer) {
                $admins->push($officer);
            }
        }

        return $admins->unique('id')->filter(fn ($a) => ! empty($a->email))->values();
    }
}
