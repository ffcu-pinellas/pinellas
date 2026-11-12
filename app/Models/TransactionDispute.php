<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TransactionDispute extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'disputed_amount' => 'decimal:2',
        'provisional_credited_at' => 'datetime',
        'resolved_at' => 'datetime',
        'timeline' => 'array',
    ];

    public const CATEGORIES = [
        'unauthorized_charge' => 'Unauthorized Transaction / Account Activity',
        'duplicate_billing' => 'Duplicate Charge or Billing Error',
        'not_received' => 'Goods or Services Not Received',
        'incorrect_amount' => 'Incorrect Amount Debited',
        'other' => 'Other (Specify Reason)',
    ];

    public const STATUSES = [
        'open' => 'Open — Under Investigation',
        'provisional_credited' => 'Provisional Credit Issued',
        'resolved_fraud' => 'Resolved — In Member Favor',
        'denied' => 'Denied — Valid Charge',
    ];

    protected static bool $schemaChecked = false;

    public static function ensureSchema(): void
    {
        if (self::$schemaChecked) {
            return;
        }

        try {
            if (! Schema::hasTable('transaction_disputes')) {
                Schema::create('transaction_disputes', function (Blueprint $table) {
                    $table->id();
                    $table->string('claim_number', 32)->unique();
                    $table->unsignedBigInteger('user_id')->index();
                    $table->unsignedBigInteger('transaction_id')->index();
                    $table->string('tnx', 64)->index();
                    $table->string('wallet_type', 32)->default('default');
                    $table->decimal('disputed_amount', 20, 2);
                    $table->string('category', 64);
                    $table->string('other_reason', 255)->nullable();
                    $table->text('description');
                    $table->string('merchant_name')->nullable();
                    $table->string('evidence_path')->nullable();
                    $table->string('status', 32)->default('open')->index();
                    $table->string('provisional_credit_tnx', 64)->nullable();
                    $table->string('clawback_tnx', 64)->nullable();
                    $table->timestamp('provisional_credited_at')->nullable();
                    $table->timestamp('resolved_at')->nullable();
                    $table->unsignedBigInteger('reviewed_by')->nullable();
                    $table->string('reviewed_via', 32)->nullable();
                    $table->text('resolution_notes')->nullable();
                    $table->json('timeline')->nullable();
                    $table->softDeletes();
                    $table->timestamps();
                });
            } else {
                if (! Schema::hasColumn('transaction_disputes', 'other_reason')) {
                    Schema::table('transaction_disputes', function (Blueprint $table) {
                        $table->string('other_reason', 255)->nullable()->after('category');
                    });
                }
            }
            self::$schemaChecked = true;
        } catch (\Throwable $e) {
            Log::warning('TransactionDispute::ensureSchema warning: ' . $e->getMessage());
        }
    }

    public static function generateClaimNumber(): string
    {
        do {
            $candidate = 'DSP-' . random_int(100000, 999999);
        } while (self::withTrashed()->where('claim_number', $candidate)->exists());

        return $candidate;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function getCategoryLabelAttribute(): string
    {
        if ($this->category === 'other' && ! empty($this->other_reason)) {
            return 'Other: ' . $this->other_reason;
        }

        return self::CATEGORIES[$this->category] ?? ucfirst(str_replace('_', ' ', (string) $this->category));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['open', 'provisional_credited'], true);
    }
}
