<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ScheduledTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'created_by',
        'title',
        'amount',
        'type', // 'credit' or 'debit'
        'wallet_type', // 'default', 'primary_savings', 'ira', etc.
        'frequency', // 'biweekly_1_15', 'monthly_1st', 'monthly_15th', 'monthly_end', 'weekly_monday', 'daily', 'once'
        'next_run_date',
        'last_run_date',
        'status', // 1 = active, 0 = paused
        'execution_count',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'next_run_date' => 'date',
        'last_run_date' => 'datetime',
        'status' => 'integer',
        'execution_count' => 'integer',
    ];

    /**
     * Self-healing database check to ensure table exists on production.
     */
    public static function ensureSchema(): void
    {
        try {
            if (!Schema::hasTable('scheduled_transactions')) {
                Schema::create('scheduled_transactions', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id');
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->string('title', 255);
                    $table->decimal('amount', 15, 2);
                    $table->string('type', 20)->default('credit');
                    $table->string('wallet_type', 50)->default('default');
                    $table->string('frequency', 50)->default('monthly_1st');
                    $table->date('next_run_date');
                    $table->dateTime('last_run_date')->nullable();
                    $table->tinyInteger('status')->default(1);
                    $table->integer('execution_count')->default(0);
                    $table->text('notes')->nullable();
                    $table->timestamps();

                    $table->index('user_id');
                    $table->index('status');
                    $table->index('next_run_date');
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Scheduled transactions schema check error: " . $e->getMessage());
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function getWalletLabelAttribute(): string
    {
        return match ($this->wallet_type) {
            'default' => 'Checking',
            'primary_savings' => 'Primary Savings',
            'ira' => 'IRA',
            'heloc' => 'HELOC',
            'cc' => 'Credit Card',
            'loan' => 'Loan Account',
            default => ucfirst(str_replace('_', ' ', (string)$this->wallet_type))
        };
    }

    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->frequency) {
            'biweekly_1_15' => 'Bi-Weekly (1st & 15th)',
            'monthly_1st' => 'Monthly (1st of Month)',
            'monthly_15th' => 'Monthly (15th of Month)',
            'monthly_end' => 'Monthly (End of Month)',
            'weekly_monday' => 'Weekly (Every Monday)',
            'daily' => 'Daily',
            'once' => 'One-Time (Future Date)',
            default => ucfirst(str_replace('_', ' ', (string)$this->frequency))
        };
    }
}

