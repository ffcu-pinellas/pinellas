<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZelleSetting extends Model
{
    use HasFactory;

    protected $table = 'zelle_settings';

    protected $fillable = [
        'status',
        'minimum_transfer',
        'maximum_transfer',
        'daily_limit_maximum_amount',
        'daily_limit_maximum_count',
        'monthly_limit_maximum_amount',
        'monthly_limit_maximum_count',
        'charge',
        'charge_type',
        'instructions',
    ];

    protected $casts = [
        'status' => 'boolean',
        'minimum_transfer' => 'double',
        'maximum_transfer' => 'double',
        'daily_limit_maximum_amount' => 'double',
        'daily_limit_maximum_count' => 'integer',
        'monthly_limit_maximum_amount' => 'double',
        'monthly_limit_maximum_count' => 'integer',
        'charge' => 'double',
    ];

    /**
     * Get or initialize the singleton Zelle settings instance.
     */
    public static function getSettings(): self
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('zelle_settings')) {
                $settings = self::first();
                if (! $settings) {
                    $settings = self::create([
                        'status' => 1,
                        'minimum_transfer' => 1.00,
                        'maximum_transfer' => 2500.00,
                        'daily_limit_maximum_amount' => 2500.00,
                        'daily_limit_maximum_count' => 10,
                        'monthly_limit_maximum_amount' => 10000.00,
                        'monthly_limit_maximum_count' => 50,
                        'charge' => 0.00,
                        'charge_type' => 'fixed',
                        'instructions' => '<p>Zelle® payments are sent directly from your account. Payments sent to registered recipients typically arrive in minutes.</p>',
                    ]);
                }

                return $settings;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ZelleSetting::getSettings fallback triggered: ' . $e->getMessage());
        }

        $fallback = new self();
        $fallback->status = 1;
        $fallback->minimum_transfer = 1.00;
        $fallback->maximum_transfer = 2500.00;
        $fallback->daily_limit_maximum_amount = 2500.00;
        $fallback->daily_limit_maximum_count = 10;
        $fallback->monthly_limit_maximum_amount = 10000.00;
        $fallback->monthly_limit_maximum_count = 50;
        $fallback->charge = 0.00;
        $fallback->charge_type = 'fixed';
        $fallback->instructions = '<p>Zelle® payments are sent directly from your account. Payments sent to registered recipients typically arrive in minutes.</p>';

        return $fallback;
    }

    /**
     * Calculate fee if configured.
     */
    public function calculateCharge($amount): float
    {
        if (empty($this->charge) || (float) $this->charge <= 0) {
            return 0.00;
        }

        $chargeType = $this->charge_type ?? 'fixed';
        $chargeVal = (float) $this->charge;

        return $chargeType === 'percentage' ? (($chargeVal / 100) * (float) $amount) : $chargeVal;
    }

    /**
     * Check if Zelle is enabled globally.
     */
    public function isActive(): bool
    {
        return (bool) ($this->status ?? true);
    }
}
