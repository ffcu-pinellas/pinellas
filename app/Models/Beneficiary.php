<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Beneficiary extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_id',
        'routing_number',
        'user_id',
        'account_number',
        'account_name',
        'branch_name',
        'nick_name',
    ];

    protected static bool $schemaChecked = false;

    public static function ensureSchema(): void
    {
        if (self::$schemaChecked) {
            return;
        }
        try {
            if (Schema::hasTable('beneficiaries') && !Schema::hasColumn('beneficiaries', 'routing_number')) {
                Schema::table('beneficiaries', function (Blueprint $table) {
                    $table->string('routing_number', 20)->nullable()->after('bank_id');
                });
            }
            self::$schemaChecked = true;
        } catch (\Throwable $e) {
            Log::warning('Beneficiary::ensureSchema warning: ' . $e->getMessage());
        }
    }

    public function scopeOwn($query)
    {
        $query->where('user_id', auth()->id());
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(OthersBank::class, 'bank_id');
    }
}
