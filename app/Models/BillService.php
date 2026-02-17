<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillService extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_id',
        'code',
        'method',
        'name',
        'currency',
        'country',
        'country_code',
        'provider_code',
        'type',
        'label',
        'data',
        'amount',
        'min_amount',
        'max_amount',
    ];

    protected $casts = [
        'label' => 'array',
        'data' => 'array',
    ];

    public function bills()
    {
        return $this->hasMany(Bill::class, 'bill_service_id');
    }
}
