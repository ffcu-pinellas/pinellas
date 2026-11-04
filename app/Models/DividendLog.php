<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DividendLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'balance_before' => 'decimal:2',
        'apy_rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
