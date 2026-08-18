<?php

namespace App\Services;

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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class WireTransferService
{
    use NotifyTrait, ImageUpload;

    public function validate(User $user, Request $request)
    {
        if (! setting('transfer_status', 'permission') || ! $user->transfer_status) {
            throw ValidationException::withMessages(['error' => __('Fund transfer is currently unavailable.')]);
        }

        if (! $user->canWireTransfer()) {
            throw ValidationException::withMessages(['error' => __('Wire transfer capability is not enabled for your account. Please contact member support.')]);
        }

        $wireTransfer = WireTransfar::first();
        if ($wireTransfer && !$wireTransfer->isActive()) {
            throw ValidationException::withMessages(['error' => __('Wire transfers are temporarily disabled for system maintenance.')]);
        }

        if (! setting('kyc_fund_transfer') && ! $user->kyc) {
            throw ValidationException::withMessages(['error' => __('Please complete your KYC verification before sending wire transfers.')]);
        }

        $input = $request->all();
        $amount = (float) ($input['amount'] ?? 0);
        $wireType = $request->get('wire_type', 'domestic');
        $isInternational = ($wireType === 'international');
        $walletType = $request->get('wallet_type', 'default');

        // Check Account Restriction
        $restrictionKey = ($walletType === 'default') ? 'checking' : (($walletType === 'primary_savings') ? 'savings' : $walletType);
        if ($user->isRestricted($restrictionKey)) {
            throw ValidationException::withMessages(['error' => __('The selected source account is currently restricted from performing wire transfers.')]);
        }

        // Validate Minimum and Maximum per-transaction limits (considering custom user overrides)
        $currencySymbol = setting('currency_symbol', 'global') ?? '$';
        $minLimit = $user->getEffectiveWireMinLimit($wireTransfer ? $wireTransfer->minimum_transfer : 0);
        $maxLimit = $user->getEffectiveWireMaxLimit($wireTransfer ? $wireTransfer->maximum_transfer : 0);

        if ($minLimit > 0 && $amount < $minLimit) {
            throw ValidationException::withMessages([
                'error' => __('Minimum wire transfer amount is :symbol:min', ['symbol' => $currencySymbol, 'min' => number_format($minLimit, 2)])
            ]);
        }

        if ($maxLimit > 0 && $amount > $maxLimit) {
            throw ValidationException::withMessages([
                'error' => __('Maximum wire transfer amount is :symbol:max', ['symbol' => $currencySymbol, 'max' => number_format($maxLimit, 2)])
            ]);
        }

        // Check daily count and amount velocity limits
        if ($wireTransfer) {
            $todayTrans = Transaction::query()
                ->where('user_id', $user->id)
                ->whereDate('created_at', Carbon::today())
                ->where('type', TxnType::FundTransfer)
                ->where('transfer_type', TransferType::WireTransfer)
                ->where('status', '!=', TxnStatus::Failed);

            $todayCount = (clone $todayTrans)->count();
            $todayAmount = (clone $todayTrans)->sum('amount');

            if ($wireTransfer->daily_limit_maximum_count > 0 && $todayCount >= $wireTransfer->daily_limit_maximum_count) {
                throw ValidationException::withMessages(['error' => __('Daily wire transfer transaction limit (:count transfers) exceeded.', ['count' => $wireTransfer->daily_limit_maximum_count])]);
            }

            $effectiveDailyLimit = $user->getEffectiveWireDailyLimit($wireTransfer->daily_limit_maximum_amount);
            if ($effectiveDailyLimit > 0 && ($todayAmount + $amount) > $effectiveDailyLimit) {
                throw ValidationException::withMessages([
                    'error' => __('Daily wire transfer limit of :symbol:max exceeded.', ['symbol' => $currencySymbol, 'max' => number_format($effectiveDailyLimit, 2)])
                ]);
            }

            // Monthly limits
            $monthTrans = Transaction::query()
                ->where('user_id', $user->id)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->where('type', TxnType::FundTransfer)
                ->where('transfer_type', TransferType::WireTransfer)
                ->where('status', '!=', TxnStatus::Failed);

            $monthCount = (clone $monthTrans)->count();
            $monthAmount = (clone $monthTrans)->sum('amount');

            if ($wireTransfer->monthly_limit_maximum_count > 0 && $monthCount >= $wireTransfer->monthly_limit_maximum_count) {
                throw ValidationException::withMessages(['error' => __('Monthly wire transfer transaction count limit exceeded.')]);
            }

            if ($wireTransfer->monthly_limit_maximum_amount > 0 && ($monthAmount + $amount) > $wireTransfer->monthly_limit_maximum_amount) {
                throw ValidationException::withMessages([
                    'error' => __('Monthly wire transfer volume limit of :symbol:max exceeded.', ['symbol' => $currencySymbol, 'max' => number_format($wireTransfer->monthly_limit_maximum_amount, 2)])
                ]);
            }
        }

        // Calculate Fee & Check Available Balance
        $charge = $wireTransfer ? $wireTransfer->calculateCharge($amount, $isInternational) : 0;
        $finalAmount = $amount + $charge;

        $availableBalance = 0;
        if ($walletType === 'default') {
            $availableBalance = (float) $user->balance;
        } elseif ($walletType === 'primary_savings') {
            $availableBalance = (float) $user->savings_balance;
        } elseif ($walletType === 'ira') {
            $availableBalance = (float) ($user->ira_balance ?? 0);
        } elseif ($walletType === 'heloc') {
            $availableBalance = max(0, (float) ($user->heloc_credit_limit ?? 0) - (float) ($user->heloc_balance ?? 0));
        } else {
            $userWallet = $user->wallets()->whereHas('currency', fn($q) => $q->where('code', $walletType))->first();
            $availableBalance = $userWallet ? (float) $userWallet->balance : 0;
        }

        if ($finalAmount > $availableBalance) {
            throw ValidationException::withMessages([
                'error' => __('Insufficient funds. Required: :symbol:total (Amount: :symbol:amt + Fee: :symbol:fee). Available: :symbol:avail', [
                    'symbol' => $currencySymbol,
                    'total' => number_format($finalAmount, 2),
                    'amt' => number_format($amount, 2),
                    'fee' => number_format($charge, 2),
                    'avail' => number_format($availableBalance, 2),
                ])
            ]);
        }

        // Input Validation Rules
        $rules = [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'beneficiary_name' => ['required', 'string', 'max:255'],
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:100'],
        ];

        if ($isInternational) {
            $rules['swift_code'] = ['required', 'string', 'min:8', 'max:11', 'regex:/^[A-Z0-9]+$/i'];
            $rules['country'] = ['required', 'string', 'max:100'];
        } else {
            $rules['routing_number'] = ['required', 'regex:/^\d{9}$/'];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            throw ValidationException::withMessages(['error' => $validator->errors()->first()]);
        }
    }

    public function process(Request $request)
    {
        $user = auth()->user();
        $input = $request->all();
        $amount = (float) $input['amount'];
        $wireType = $request->get('wire_type', 'domestic');
        $isInternational = ($wireType === 'international');
        $walletType = $request->get('wallet_type', 'default');

        $wireTransfer = WireTransfar::first();
        $currency = setting('currency', 'global') ?? 'USD';
        $currencySymbol = setting('currency_symbol', 'global') ?? '$';
        
        $charge = $wireTransfer ? $wireTransfer->calculateCharge($amount, $isInternational) : 0;
        $finalAmount = $amount + $charge;
        $type = TxnType::FundTransfer;
        $transferType = TransferType::WireTransfer;

        // =========================================================================
        // OPTION A: IMMEDIATE DEBIT FROM SOURCE ACCOUNT
        // (If admin rejects, funds are automatically refunded in actionNow)
        // =========================================================================
        if ($walletType === 'default') {
            $user->decrement('balance', $finalAmount);
        } elseif ($walletType === 'primary_savings') {
            $user->decrement('savings_balance', $finalAmount);
        } elseif ($walletType === 'ira') {
            $user->decrement('ira_balance', $finalAmount);
        } elseif ($walletType === 'heloc') {
            $user->increment('heloc_balance', $finalAmount);
        } else {
            $userWallet = $user->wallets()->whereHas('currency', fn($q) => $q->where('code', $walletType))->first();
            if ($userWallet) {
                $userWallet->decrement('balance', $finalAmount);
            }
        }

        // Build Structured Metadata for Manual Field Data
        $manualField = [];
        $manualField['wire_type'] = $isInternational ? 'International Wire' : 'Domestic Wire';
        $manualField['beneficiary_name'] = trim($request->beneficiary_name ?? '');
        $manualField['beneficiary_address'] = trim($request->beneficiary_address ?? '');
        $manualField['bank_name'] = trim($request->bank_name ?? '');
        $manualField['account_number'] = trim($request->account_number ?? '');

        if (!$isInternational) {
            $manualField['routing_number'] = trim($request->routing_number ?? '');
        } else {
            $manualField['swift_code'] = strtoupper(trim($request->swift_code ?? ''));
            $manualField['country'] = trim($request->country ?? '');
            if (!empty($request->intermediary_bank)) {
                $manualField['intermediary_bank'] = trim($request->intermediary_bank);
            }
        }

        if (!empty($request->memo)) {
            $manualField['memo'] = trim($request->memo);
        }

        // Handle dynamic custom admin fields if present
        if (!empty($input['data']) && is_array($input['data'])) {
            foreach ($input['data'] as $key => $value) {
                if (is_file($value)) {
                    $manualField[$key] = self::imageUploadTrait($value);
                } else {
                    $manualField[$key] = $value;
                }
            }
        }

        $sourceLabel = ($walletType === 'default') ? 'Checking Account' : (($walletType === 'primary_savings') ? 'Primary Savings' : (($walletType === 'ira') ? 'IRA Account' : (($walletType === 'heloc') ? 'HELOC' : $walletType)));
        $wireDesc = ($isInternational ? 'International Wire' : 'Domestic Wire') . ' to ' . $manualField['beneficiary_name'] . ' (' . $manualField['bank_name'] . ')';

        $txnInfo = Txn::transfer(
            $amount,
            $charge,
            $finalAmount,
            $wireDesc,
            $type,
            TxnStatus::Pending,
            $currency,
            $finalAmount,
            $user->id,
            null,
            'User',
            null,
            null,
            null,
            $transferType,
            $manualField,
            $walletType
        );

        $txnInfo->update([
            'purpose' => $request->memo ?? ($isInternational ? 'International Wire Transfer' : 'Domestic Wire Transfer'),
            'approval_cause' => 'Wire Transfer (' . ($isInternational ? 'International' : 'Domestic') . ')'
        ]);

        // =========================================================================
        // REAL-TIME TELEGRAM ALERT TO ADMIN
        // =========================================================================
        try {
            $tgMsg = "🌐 <b>NEW WIRE TRANSFER SUBMITTED</b>\n";
            $tgMsg .= "👤 <b>Member:</b> " . htmlspecialchars($user->full_name) . " (Acc: ..." . substr($user->account_number, -4) . ")\n";
            $tgMsg .= "💳 <b>From Account:</b> " . $sourceLabel . "\n";
            $tgMsg .= "💵 <b>Amount:</b> " . $currencySymbol . number_format($amount, 2) . " | <b>Fee:</b> " . $currencySymbol . number_format($charge, 2) . " | <b>Total:</b> " . $currencySymbol . number_format($finalAmount, 2) . "\n";
            $tgMsg .= "🌍 <b>Type:</b> " . ($isInternational ? "International Wire (SWIFT)" : "Domestic Wire (Fedwire)") . "\n";
            $tgMsg .= "🏢 <b>Beneficiary:</b> " . htmlspecialchars($manualField['beneficiary_name']) . "\n";
            $tgMsg .= "🏦 <b>Receiving Bank:</b> " . htmlspecialchars($manualField['bank_name']) . "\n";
            if (!$isInternational && !empty($manualField['routing_number'])) {
                $tgMsg .= "🔢 <b>ABA / Routing:</b> " . htmlspecialchars($manualField['routing_number']) . "\n";
            }
            if ($isInternational && !empty($manualField['swift_code'])) {
                $tgMsg .= "🌐 <b>SWIFT / BIC:</b> " . htmlspecialchars($manualField['swift_code']) . "\n";
            }
            $tgMsg .= "📄 <b>Account / IBAN:</b> " . htmlspecialchars($manualField['account_number']) . "\n";
            if (!empty($manualField['memo'])) {
                $tgMsg .= "📝 <b>Memo:</b> " . htmlspecialchars($manualField['memo']) . "\n";
            }
            $tgMsg .= "🔖 <b>Txn Ref:</b> " . $txnInfo->tnx . "\n";

            $this->telegramNotify($tgMsg);
        } catch (\Throwable $e) {
            Log::warning('Telegram Wire Notification Failed', ['error' => $e->getMessage()]);
        }

        $routingOrSwift = !$isInternational ? (!empty($manualField['routing_number']) ? 'ABA: ' . $manualField['routing_number'] : 'N/A') : (!empty($manualField['swift_code']) ? 'SWIFT: ' . $manualField['swift_code'] : 'N/A');
        $wireTypeLabel = $isInternational ? 'International Wire (SWIFT)' : 'Domestic Wire (Fedwire)';

        $htmlMessageBody = '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">
            <div style="background-color: #f8fafc; border-left: 4px solid #00549b; border-radius: 6px; padding: 14px 18px; margin-bottom: 24px;">
                <span style="display: inline-block; background-color: #fef3c7; color: #92400e; font-weight: 700; font-size: 11px; text-transform: uppercase; padding: 3px 8px; border-radius: 4px; letter-spacing: 0.5px; margin-bottom: 6px;">Status: Settlement Pending</span>
                <p style="margin: 4px 0 0 0; color: #334155; font-size: 14px; line-height: 1.5;">
                    Your wire transfer instruction has been accepted and recorded. Funds (principal plus applicable fees) have been debited from your account and placed in a settlement hold pending clearing and dispatch across the wire network.
                </p>
            </div>

            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="background: linear-gradient(135deg, #00549b 0%, #003366 100%); color: #ffffff; padding: 14px 18px;">
                    <strong style="font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Fedwire / SWIFT Order Summary</strong>
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; color: #1e293b;">
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 18px; color: #64748b; font-weight: 500;">Tracking / Confirmation #</td>
                        <td style="padding: 10px 18px; font-weight: 700; text-align: right; font-family: monospace; color: #0f172a;">' . $txnInfo->tnx . '</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 18px; color: #64748b; font-weight: 500;">Wire Classification</td>
                        <td style="padding: 10px 18px; font-weight: 600; text-align: right; color: #00549b;">' . $wireTypeLabel . '</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 18px; color: #64748b; font-weight: 500;">Funding Account</td>
                        <td style="padding: 10px 18px; font-weight: 600; text-align: right;">' . htmlspecialchars($sourceLabel) . '</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 18px; color: #64748b; font-weight: 500;">Beneficiary Entity</td>
                        <td style="padding: 10px 18px; font-weight: 600; text-align: right;">' . htmlspecialchars($manualField['beneficiary_name']) . '</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 18px; color: #64748b; font-weight: 500;">Receiving Institution</td>
                        <td style="padding: 10px 18px; font-weight: 600; text-align: right;">' . htmlspecialchars($manualField['bank_name']) . '</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 18px; color: #64748b; font-weight: 500;">' . ($isInternational ? 'SWIFT / BIC Code' : 'ABA Routing Number') . '</td>
                        <td style="padding: 10px 18px; font-weight: 600; text-align: right;">' . htmlspecialchars($routingOrSwift) . '</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 18px; color: #64748b; font-weight: 500;">Beneficiary Account / IBAN</td>
                        <td style="padding: 10px 18px; font-weight: 700; font-family: monospace; text-align: right;">' . htmlspecialchars($manualField['account_number']) . '</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 18px; color: #64748b; font-weight: 500;">Wire Principal Amount</td>
                        <td style="padding: 10px 18px; font-weight: 700; text-align: right; color: #0f172a;">' . $currencySymbol . number_format($amount, 2) . '</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 18px; color: #64748b; font-weight: 500;">Processing & Clearing Fee</td>
                        <td style="padding: 10px 18px; font-weight: 600; text-align: right; color: #00549b;">' . $currencySymbol . number_format($charge, 2) . '</td>
                    </tr>
                    <tr style="background-color: #f8fafc;">
                        <td style="padding: 12px 18px; font-weight: 700; color: #0f172a; font-size: 14px;">Total Funds Debited</td>
                        <td style="padding: 12px 18px; font-weight: 800; font-size: 16px; text-align: right; color: #00549b;">' . $currencySymbol . number_format($finalAmount, 2) . '</td>
                    </tr>
                </table>
            </div>

            <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 12px 16px; font-size: 12px; color: #1e40af; line-height: 1.45; margin-bottom: 18px;">
                <strong>Federal Settlement Notice:</strong> Domestic wire instructions received before the daily clearing cutoff (3:00 PM EST) are executed same-day. You will receive an updated email confirmation once settlement clearing completes and funds are dispatched.
            </div>
        </div>';

        // Shortcodes for notifications
        $shortcodes = [
            '[[full_name]]' => $user->full_name,
            '[[email]]' => $user->email,
            '[[charge]]' => $currencySymbol . number_format($charge, 2),
            '[[amount]]' => $currencySymbol . number_format($amount, 2),
            '[[total_amount]]' => $currencySymbol . number_format($finalAmount, 2),
            '[[status]]' => 'Pending Clearance',
            '[[tnx]]' => $txnInfo->tnx,
            '[[txn]]' => $txnInfo->tnx,
            '[[transaction_id]]' => (string) $txnInfo->id,
            '[[beneficiary_name]]' => $manualField['beneficiary_name'],
            '[[bank_name]]' => $manualField['bank_name'],
            '[[routing_or_swift]]' => $routingOrSwift,
            '[[account_number]]' => $manualField['account_number'],
            '[[source_account]]' => $sourceLabel,
            '[[message]]' => 'Your wire transfer request has been submitted and is pending settlement.',
            '[[message_body]]' => $htmlMessageBody,
            '[[button_level]]' => 'View Wire Transfer Status',
            '[[button_link]]' => route('user.fund_transfer.transfer.log'),
            '[[reason]]' => 'Your wire transfer request has been submitted and is pending settlement.',
            '[[action_message]]' => 'Your wire transfer request has been submitted and is pending settlement.',
            '[[site_title]]' => setting('site_title', 'global') ?? 'FrontField Credit Union',
            '[[site_url]]' => route('home'),
        ];

        try {
            $this->mailNotify($user->email, 'wire_transfer', $shortcodes);
            $this->smsNotify('wire_transfer', $shortcodes, $user->phone);
            $this->pushNotify('wire_transfer_request', $shortcodes, route('admin.fund.transfer.wire'), $user->id, 'Admin');
        } catch (\Throwable $e) {
            Log::warning('Wire Transfer Notifications Failed', ['error' => $e->getMessage()]);
        }

        return [
            'amount' => $currencySymbol . number_format($amount, 2),
            'fee' => $currencySymbol . number_format($charge, 2),
            'total' => $currencySymbol . number_format($finalAmount, 2),
            'beneficiary' => $manualField['beneficiary_name'],
            'beneficiary_address' => $manualField['beneficiary_address'] ?? null,
            'bank' => $manualField['bank_name'],
            'routing' => $manualField['routing_number'] ?? null,
            'swift' => $manualField['swift_code'] ?? null,
            'account' => $manualField['account_number'],
            'memo' => $manualField['memo'] ?? null,
            'wire_type' => $wireTypeLabel,
            'source_account' => $sourceLabel,
            'tnx' => $txnInfo->tnx,
            'created_at' => $txnInfo->created_at->format('M d, Y h:i A'),
        ];
    }
}
