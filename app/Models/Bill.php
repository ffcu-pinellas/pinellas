<?php

namespace App\Models;

use App\Enums\BillStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_service_id',
        'user_id',
        'data',
        'response_data',
        'amount',
        'charge',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
        'status' => BillStatus::class,
    ];

    public function service()
    {
        return $this->belongsTo(BillService::class, 'bill_service_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
