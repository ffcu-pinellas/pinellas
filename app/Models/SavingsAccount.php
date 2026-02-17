<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_number',
        'type',
        'balance',
        'interest_rate',
        'status',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->account_number = self::generateUniqueAccountNumber();
        });
    }

    private static function generateUniqueAccountNumber()
    {
        $limit = setting('account_number_limit', 'global') ?? 12;
        
        do {
            $accountNumber = random_int(1000000000000000, 9999999999999999);
            $accountNumber = substr((string)$accountNumber, 0, $limit);
        } while (self::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
