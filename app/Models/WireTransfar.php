<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WireTransfar extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'minimum_transfer',
        'maximum_transfer',
        'charge',
        'charge_type',
        'international_charge',
        'international_charge_type',
        'daily_limit_maximum_amount',
        'daily_limit_maximum_count',
        'monthly_limit_maximum_amount',
        'monthly_limit_maximum_count',
        'instructions',
        'field_options',
    ];

    protected $casts = [
        'status' => 'boolean',
        'minimum_transfer' => 'double',
        'maximum_transfer' => 'double',
        'charge' => 'double',
        'international_charge' => 'double',
        'daily_limit_maximum_amount' => 'double',
        'daily_limit_maximum_count' => 'integer',
        'monthly_limit_maximum_amount' => 'double',
        'monthly_limit_maximum_count' => 'integer',
    ];

    /**
     * Calculate effective wire fee based on transfer amount and wire mode (Domestic vs International).
     */
    public function calculateCharge($amount, $isInternational = false): float
    {
        if ($isInternational && !empty($this->international_charge)) {
            $chargeType = $this->international_charge_type ?? 'fixed';
            $chargeVal = (float) $this->international_charge;
            return $chargeType === 'percentage' ? (($chargeVal / 100) * (float) $amount) : $chargeVal;
        }

        $chargeType = $this->charge_type ?? 'fixed';
        $chargeVal = (float) $this->charge;
        return $chargeType === 'percentage' ? (($chargeVal / 100) * (float) $amount) : $chargeVal;
    }

    /**
     * Check if wire transfers are enabled globally.
     */
    public function isActive(): bool
    {
        return (bool) ($this->status ?? true);
    }
}
