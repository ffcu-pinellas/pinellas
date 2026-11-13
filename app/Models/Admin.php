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

    protected $guard_name = 'admin';

    protected $fillable = [
        'avatar', 
        'name', 
        'email', 
        'phone', 
        'password', 
        'passcode', 
        'passcode_status', 
        'fcm_token', 
        'is_admin', 
        'status',
        'allowed_institutions',
        'onboarding_code',
        'onboarding_views_count',
        'onboarding_signups_count'
    ];

    /**
     * Self-healing database check to ensure onboarding and institution columns exist on production.
     */
    public static function ensureOnboardingSchema(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('admins', 'allowed_institutions')) {
                \Illuminate\Support\Facades\Schema::table('admins', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('admins', 'allowed_institutions')) {
                        $table->text('allowed_institutions')->nullable()->after('status');
                    }
                });
            }

            if (!\Illuminate\Support\Facades\Schema::hasColumn('admins', 'onboarding_views_count')) {
                \Illuminate\Support\Facades\Schema::table('admins', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('admins', 'onboarding_code')) {
                        $table->string('onboarding_code', 100)->nullable()->unique()->after('status');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('admins', 'onboarding_views_count')) {
                        $table->unsignedInteger('onboarding_views_count')->default(0)->after('onboarding_code');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('admins', 'onboarding_signups_count')) {
                        $table->unsignedInteger('onboarding_signups_count')->default(0)->after('onboarding_views_count');
                    }
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Self-healing admin onboarding/institution columns error: " . $e->getMessage());
        }
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('Super-Admin');
    }

    /**
     * Get the list of allowed institution IDs for this admin/officer.
     * ['*'] denotes access to all institutions.
     */
    public function getAllowedInstitutionsList(): array
    {
        if ($this->isSuperAdmin() || empty($this->allowed_institutions)) {
            return ['*'];
        }

        $val = is_array($this->allowed_institutions)
            ? $this->allowed_institutions
            : json_decode($this->allowed_institutions, true);

        if (!is_array($val)) {
            $val = array_filter(array_map('trim', explode(',', (string)$this->allowed_institutions)));
        }

        return !empty($val) ? $val : ['*'];
    }

    /**
     * Determine whether this admin/officer has access to a specific institution.
     */
    public function hasInstitutionAccess($institutionId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $allowed = $this->getAllowedInstitutionsList();
        if (in_array('*', $allowed) || in_array('all', $allowed)) {
            return true;
        }

        return in_array((string)$institutionId, array_map('strval', $allowed));
    }

    public function managedUsers()
    {
        return $this->hasMany(User::class, 'staff_id');
    }

    /**
     * Get the officer's primary shareable onboarding URL.
     */
    public function getOnboardingUrlAttribute(): string
    {
        if (!empty($this->onboarding_code)) {
            return url('/join/' . $this->onboarding_code);
        }
        return url('/' . $this->id);
    }
}
