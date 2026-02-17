<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemoteDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_name',
        'account_number',
        'amount',
        'front_image',
        'back_image',
        'status',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
