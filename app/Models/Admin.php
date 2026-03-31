<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = ['avatar', 'name', 'email', 'phone', 'password', 'passcode', 'passcode_status', 'fcm_token', 'is_admin', 'status'];

    public function isSuperAdmin()
    {
        return $this->hasRole('Super-Admin');
    }

    public function managedUsers()
    {
        return $this->hasMany(User::class, 'staff_id');
    }
}
