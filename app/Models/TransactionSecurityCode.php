<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionSecurityCode extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'type',
        'tries',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
