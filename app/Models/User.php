<?php

namespace App\Models;

use App\Enums\KYCStatus;
use App\Enums\TxnStatus;
use App\Enums\TxnType;
use Carbon\Carbon;
use Coderflex\LaravelTicket\Concerns\HasTickets;
use Coderflex\LaravelTicket\Contracts\CanUseTickets;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|User search($search)
 */
class User extends Authenticatable implements CanUseTickets, MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasTickets, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'preferred_first_name',
        'portfolio_id',
        'branch_id',
        'portfolios',
        'avatar',
        'first_name',
        'last_name',
        'country',
        'phone',
        'username',
        'email',
        'email_verified_at',
        'gender',
        'date_of_birth',
        'city',
        'zip_code',
        'address',
        'balance',
        'points',
        'account_number',
        'status',
        'kyc',
        'kyc_credential',
        'google2fa_secret',
        'two_fa',
        'deposit_status',
        'withdraw_status',
        'transfer_status',
        'otp_status',
        'dps_status',
        'fdr_status',
        'loan_status',
        'portfolio_status',
        'reward_status',
        'referral_status',
        'ref_id',
        'ssn',
        'password',
        'passcode',
        'phone_verified',
        'otp',
        'notifications_permission',
        'transaction_pin',
        'security_preference',
        'close_reason',
        'savings_account_number',
        'savings_balance',
        'ira_status',
        'ira_account_number',
        'ira_balance',
        'heloc_status',
        'heloc_account_number',
        'heloc_balance',
        'heloc_credit_limit',
        'cc_status',
        'cc_account_number',
        'cc_balance',
        'cc_credit_limit',
        'loan_account_status',
        'loan_account_number',
        'loan_balance',
        'loan_original_amount',
        'dashboard_order',
        'staff_id',
        'fcm_token',
        'checking_restricted',
        'savings_restricted',
        'ira_restricted',
        'heloc_restricted',
        'cc_restricted',
        'loan_restricted',
        'wire_transfer_status',
        'custom_wire_min_limit',
        'custom_wire_max_limit',
        'custom_wire_daily_limit',
        'zelle_transfer_status',
        'custom_zelle_min_limit',
        'custom_zelle_max_limit',
        'custom_zelle_daily_limit',
        'custom_zelle_monthly_limit',
        'dividend_status',
        'dividend_apy',
        'dividend_wallet',
        'last_dividend_at',
        'institution_id',
    ];

    // ─── Institution Relationship ─────────────────────────────────────────────

    public function institution()
    {
        return $this->belongsTo(\App\Models\Institution::class);
    }

    /**
     * Return the user's institution, falling back to the default if not set.
     */
    public function getInstitutionAttribute(): \App\Models\Institution
    {
        return $this->institution()->first()
            ?? \App\Models\Institution::getDefault()
            ?? new \App\Models\Institution([
                'name'          => 'FrontField Credit Union',
                'short_name'    => 'FFCU',
                'primary_color' => '#00549b',
            ]);
    }

    public function staff()
    {
        return $this->belongsTo(Admin::class, 'staff_id');
    }

    protected $appends = [
        'full_name',
        'kyc_time',
        'kyc_type',
        'total_profit',
        'total_deposit',
        'avatar_path',
    ];

    protected $dates = ['kyc_time'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_fa' => 'boolean',
        'phone_verified' => 'boolean',
        'notifications_permission' => 'array',
        'dashboard_order' => 'array',
        'dividend_status' => 'boolean',
        'dividend_apy' => 'decimal:2',
        'last_dividend_at' => 'datetime',
    ];

    /*
     * Scope Declaration
     * */

    public function scopeDividendEligible($query)
    {
        return $query->where('dividend_status', 1)->where('status', 1);
    }

    public function scopeSearch($query, $search)
    {
        if ($search != null) {
            return $query->where(function ($query) use ($search) {
                $query->orWhere('first_name', 'LIKE', '%'.$search.'%')
                    ->orWhere('last_name', 'LIKE', '%'.$search.'%')
                    ->orWhere('username', 'LIKE', '%'.$search.'%')
                    ->orWhere('email', 'LIKE', '%'.$search.'%')
                    ->orWhere('phone', 'LIKE', '%'.$search.'%')
                    ->orWhere('account_number', 'LIKE', '%'.$search.'%')
                    ->orWhere('ssn', 'LIKE', '%'.$search.'%');
            });
        }

        return $query;
    }

    public function isRestricted($accountType)
    {
        $field = "{$accountType}_restricted";
        if ($accountType === 'checking') $field = 'checking_restricted';
        if ($accountType === 'savings') $field = 'savings_restricted';
        if ($accountType === 'wire') return !$this->canWireTransfer();
        
        return (bool) ($this->$field ?? false);
    }

    public function canWireTransfer(): bool
    {
        if (isset($this->wire_transfer_status) && (int) $this->wire_transfer_status === 0) {
            return false;
        }
        return (bool) ($this->transfer_status ?? true);
    }

    public function getEffectiveWireMinLimit($defaultLimit = 0): float
    {
        return (isset($this->custom_wire_min_limit) && $this->custom_wire_min_limit !== '' && is_numeric($this->custom_wire_min_limit))
            ? (float) $this->custom_wire_min_limit
            : (float) $defaultLimit;
    }

    public function getEffectiveWireMaxLimit($defaultLimit = 0): float
    {
        return (isset($this->custom_wire_max_limit) && $this->custom_wire_max_limit !== '' && is_numeric($this->custom_wire_max_limit))
            ? (float) $this->custom_wire_max_limit
            : (float) $defaultLimit;
    }

    public function getEffectiveWireDailyLimit($defaultLimit = 0): float
    {
        return (isset($this->custom_wire_daily_limit) && $this->custom_wire_daily_limit !== '' && is_numeric($this->custom_wire_daily_limit))
            ? (float) $this->custom_wire_daily_limit
            : (float) $defaultLimit;
    }

    public function canZelleTransfer(): bool
    {
        if (isset($this->zelle_transfer_status) && (int) $this->zelle_transfer_status === 0) {
            return false;
        }
        return (bool) ($this->transfer_status ?? true);
    }

    public function getEffectiveZelleMinLimit($defaultLimit = 1.00): float
    {
        return (isset($this->custom_zelle_min_limit) && $this->custom_zelle_min_limit !== '' && is_numeric($this->custom_zelle_min_limit))
            ? (float) $this->custom_zelle_min_limit
            : (float) $defaultLimit;
    }

    public function getEffectiveZelleMaxLimit($defaultLimit = 2500.00): float
    {
        return (isset($this->custom_zelle_max_limit) && $this->custom_zelle_max_limit !== '' && is_numeric($this->custom_zelle_max_limit))
            ? (float) $this->custom_zelle_max_limit
            : (float) $defaultLimit;
    }

    public function getEffectiveZelleDailyLimit($defaultLimit = 2500.00): float
    {
        return (isset($this->custom_zelle_daily_limit) && $this->custom_zelle_daily_limit !== '' && is_numeric($this->custom_zelle_daily_limit))
            ? (float) $this->custom_zelle_daily_limit
            : (float) $defaultLimit;
    }

    public function getEffectiveZelleMonthlyLimit($defaultLimit = 10000.00): float
    {
        return (isset($this->custom_zelle_monthly_limit) && $this->custom_zelle_monthly_limit !== '' && is_numeric($this->custom_zelle_monthly_limit))
            ? (float) $this->custom_zelle_monthly_limit
            : (float) $defaultLimit;
    }

    public function scopeStatus($query, $status)
    {
        if ($status != 'all' && $status != null) {
            $status = $status == 'pending' ? KYCStatus::Pending : ($status === 'rejected' ? KYCStatus::Failed : KYCStatus::Verified);

            return $query->where('kyc', $status);
        }
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 2);
    }

    public function scopeDisabled($query)
    {
        return $query->where('status', 0);
    }

    public function getUpdatedAtAttribute(): string
    {
        return !empty($this->attributes['updated_at']) ? Carbon::parse($this->attributes['updated_at'])->format('d M Y h:i A') : '';
    }

    public function getAvatarPathAttribute()
    {
        return !empty($this->attributes['avatar']) && file_exists(base_path('assets/'.$this->attributes['avatar'])) ? asset($this->attributes['avatar']) : null;
    }

    public function getAccountNumberAttribute()
    {
        return setting('account_number_prefix', 'global').($this->attributes['account_number'] ?? '');
    }

    public function getCreatedAtAttribute(): string
    {
        return !empty($this->attributes['created_at']) ? Carbon::parse($this->attributes['created_at'])->format('d M Y h:i A') : '';
    }

    public function getFullNameAttribute(): string
    {
        return ucwords(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }

    public function getKycTypeAttribute(): string
    {
        if (empty($this->attributes['id'])) return '';
        $kycs = UserKyc::where('user_id', $this->attributes['id'])->pluck('kyc_id');

        $types = Kyc::whereIn('id', $kycs)->pluck('name')->implode(',');

        return $types;
    }

    public function getKycTimeAttribute(): string
    {
        $cred = !empty($this->attributes['kyc_credential']) ? json_decode($this->attributes['kyc_credential'], true) : [];
        return $cred['kyc_time_of_time'] ?? '';
    }

    public function getTotalProfitAttribute(): string
    {
        try {
            return (string) $this->totalProfit();
        } catch (\Throwable $e) {
            return '0.00';
        }
    }

    public function getTotalDepositAttribute(): string
    {
        try {
            return (string) $this->totalDeposit();
        } catch (\Throwable $e) {
            return '0.00';
        }
    }

    public function totalProfit($days = null)
    {

        $sum = $this->transaction()->where('status', TxnStatus::Success)->where(function ($query) {
            $query->whereIn('type', [TxnType::Referral, TxnType::SignupBonus, TxnType::PortfolioBonus, TxnType::RewardRedeem, TxnType::DpsMaturity, TxnType::FdrInstallment]);
        });

        if ($days != null) {
            $sum->where('created_at', '>=', Carbon::now()->subDays((int) $days));
        }
        $sum = $sum->sum('amount');

        return round($sum, 2);
    }

    public function totalFdr()
    {
        return $this->fdr()->sum('amount');
    }

    public function rejectedKycs()
    {
        return $this->kycs()->where('status', 'rejected');
    }

    public function kycs()
    {
        return $this->hasMany(UserKyc::class, 'user_id', 'id');
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function getReferrals()
    {
        return ReferralProgram::all()->map(function ($program) {
            return ReferralLink::getReferral($this, $program);
        });
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'ref_id');
    }

    public function totalDeposit()
    {
        $sum = $this->transaction()->where('status', TxnStatus::Success)->where(function ($query) {
            $query->where('type', TxnType::Deposit)
                ->orWhere('type', TxnType::ManualDeposit);
        })->sum('amount');

        return round($sum, 2);
    }

    public function totalDepositBonus()
    {
        $sum = $this->transaction()->where('status', TxnStatus::Success)->where(function ($query) {
            $query->where('target_id', '!=', null)
                ->where('target_type', 'deposit')
                ->where('type', TxnType::Referral);
        })->sum('amount');

        return round($sum, 2);
    }

    public function totalWithdraw()
    {
        $sum = $this->transaction()->where('status', TxnStatus::Success)->where(function ($query) {
            $query->where('type', TxnType::Withdraw)
                ->orWhere('type', TxnType::WithdrawAuto);
        })->sum('amount');

        return round($sum, 2);
    }

    public function totalTransfer()
    {
        $sum = $this->transaction()->where('status', TxnStatus::Success)->where(function ($query) {
            $query->where('type', TxnType::FundTransfer);
        })->sum('amount');

        return round($sum, 2);
    }

    public function totalReferralProfit()
    {
        $sum = $this->transaction()->where('status', TxnStatus::Success)->where(function ($query) {
            $query->where('type', TxnType::Referral);
        })->sum('amount');

        return round($sum, 2);
    }

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class, 'portfolio_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function dps()
    {
        return $this->hasMany(Dps::class, 'user_id', 'id');
    }

    public function fdr()
    {
        return $this->hasMany(Fdr::class, 'user_id', 'id');
    }

    public function loan()
    {
        return $this->hasMany(Loan::class, 'user_id', 'id');
    }



    public function ticket()
    {
        return $this->hasMany(Ticket::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function remoteDeposits()
    {
        return $this->hasMany(RemoteDeposit::class);
    }


    public function portfolioAchieved()
    {
        $portfolios = json_decode($this->portfolios, true);

        if (is_array($portfolios)) {
            return count($portfolios);
        }

        return 0;
    }

    protected function google2faSecret(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $value != null ? decrypt($value) : $value,
            set: fn ($value) => encrypt($value),
        );
    }

    public function scopeWithTrash($query)
    {
        return $query->withTrashed();
    }

    public function activities()
    {
        return $this->hasMany(LoginActivities::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Send institutional-tailored email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $institution = $this->institution ?? \App\Models\Institution::getDefault();
        $isCustom = $institution && !$institution->is_default;
        
        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            \Illuminate\Support\Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $this->getKey(),
                'hash' => sha1($this->getEmailForVerification()),
            ]
        );

        if ($isCustom) {
            $parsed = parse_url($verificationUrl);
            $targetHost = "{$institution->slug}.frontfieldcu.pro";
            $verificationUrl = "https://{$targetHost}" . ($parsed['path'] ?? '') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
        }

        $siteTitle = $institution->name;
        $primaryColor = $institution->primary_color ?? ($isCustom ? '#c8232c' : '#00549b');
        $siteLogo = $institution->logo_path ? asset('assets/' . $institution->logo_path) : ($isCustom ? asset('assets/institutions/clevelandstatebank/images/logo.png') : asset('assets/external/images/logo.png'));
        $siteLogoPath = $institution->logo_path ? public_path('assets/' . $institution->logo_path) : null;
        $regulator = $isCustom ? ('Member ' . ($institution->regulator_type ?? 'FDIC')) : 'Federally Insured by NCUA';
        $supportPhone = $institution->support_phone ?? ($isCustom ? '662-843-9461' : setting('site_phone', 'global'));
        $supportEmail = $institution->support_email ?? ($isCustom ? 'support@clevelandstatebank.com' : setting('support_email', 'global'));
        $siteLink = $isCustom ? "https://{$institution->slug}.frontfieldcu.pro" : route('home');

        $details = [
            'subject' => "Verify Your Email Address — {$siteTitle}",
            'title' => 'Email Verification Required',
            'salutation' => "Hello {$this->first_name}",
            'message_body' => "<p>Thank you for registering with <strong>{$siteTitle}</strong>. Please click the button below to verify your email address and activate your digital banking profile.</p><p style='font-size: 13px; color: #64748b;'>If you did not initiate this account creation, no further action is required.</p>",
            'button_level' => 'Verify Email Address',
            'button_link' => $verificationUrl,
            'footer_status' => true,
            'site_logo' => $siteLogo,
            'site_logo_path' => $siteLogoPath,
            'site_title' => $siteTitle,
            'site_link' => $siteLink,
            'primary_color' => $primaryColor,
            'regulator_type' => $regulator,
            'support_phone' => $supportPhone,
            'support_email' => $supportEmail,
            'from_name' => $siteTitle,
            'from_email' => $supportEmail,
        ];

        try {
            \Illuminate\Support\Facades\Mail::to($this->email)->send(new \App\Mail\MailSend($details));
        } catch (\Throwable $e) {
            \Log::error("Email verification send error: " . $e->getMessage());
        }
    }

    public function refferelLinks()
    {
        return $this->hasMany(ReferralLink::class);
    }

    public function withdrawAccounts()
    {
        return $this->hasMany(WithdrawAccount::class);
    }

    public function wallets()
    {
        return $this->hasMany(UserWallet::class, 'user_id');
    }

    public function cards()
    {
        return $this->hasMany(UserCard::class, 'user_id');
    }

    public function getNotificationPreference(string $key, $default = null)
    {
        $prefs = $this->notifications_permission ?? [];
        if (is_string($prefs)) {
            $prefs = json_decode($prefs, true) ?? [];
        }
        return data_get($prefs, $key, $default);
    }

    public function largeTxnThreshold(): float
    {
        return (float) $this->getNotificationPreference('large_txn_threshold', 250.00);
    }

    public function lowBalanceThreshold(): float
    {
        return (float) $this->getNotificationPreference('low_balance_threshold', 100.00);
    }

    public function isLargeTxnAlertEnabled(): bool
    {
        return (bool) $this->getNotificationPreference('large_txn_alert', true);
    }

    public function isLowBalanceAlertEnabled(): bool
    {
        return (bool) $this->getNotificationPreference('low_balance_alert', true);
    }

    public function isDirectDepositAlertEnabled(): bool
    {
        return (bool) $this->getNotificationPreference('direct_deposit_alert', true);
    }

    public function isCardChargeAlertEnabled(): bool
    {
        return (bool) $this->getNotificationPreference('card_charge_alert', true);
    }

    public function isNewDeviceAlertEnabled(): bool
    {
        return (bool) $this->getNotificationPreference('new_device_login_alert', true);
    }

    public function isEmailAlertsEnabled(): bool
    {
        return (bool) $this->getNotificationPreference('email_alerts_enabled', true);
    }

    public function isPushAlertsEnabled(): bool
    {
        return (bool) $this->getNotificationPreference('push_alerts_enabled', true);
    }

    protected static function booted(): void
    {
        static::created(function ($user) {

            $notifications = notificationPermissions();

            $user->update([
                'notifications_permission' => $notifications,
                'account_number' => generateAccountNumber(),
                'savings_account_number' => generateSavingsAccountNumber(),
            ]);
        });
    }
}
