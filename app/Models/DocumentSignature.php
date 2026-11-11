<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DocumentSignature extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'signed_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static bool $schemaChecked = false;

    public static function ensureSchema(): void
    {
        if (self::$schemaChecked) {
            return;
        }

        try {
            if (! Schema::hasTable('document_signatures')) {
                Schema::create('document_signatures', function ($table) {
                    $table->id();
                    $table->foreignId('document_history_id')->constrained('document_histories')->onDelete('cascade');
                    $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                    $table->foreignId('signed_by')->nullable()->constrained('admins')->onDelete('set null');
                    $table->enum('signature_type', ['electronic', 'digital', 'wet_ink'])->default('electronic');
                    $table->string('signature_provider')->default('internal');
                    $table->string('external_signature_id')->nullable()->index();
                    $table->longText('signature_data')->nullable();
                    $table->string('ip_address')->nullable();
                    $table->text('user_agent')->nullable();
                    $table->enum('status', ['pending', 'signed', 'declined', 'expired'])->default('pending');
                    $table->timestamp('signed_at')->nullable();
                    $table->timestamp('expires_at')->nullable();
                    $table->longText('metadata')->nullable();
                    $table->softDeletes();
                    $table->timestamps();
                });
            } else {
                // Ensure LONGTEXT capacity for canvas PNG base64 + frozen document snapshot
                try {
                    DB::statement('ALTER TABLE `document_signatures` MODIFY COLUMN `signature_data` LONGTEXT NULL');
                    DB::statement('ALTER TABLE `document_signatures` MODIFY COLUMN `metadata` LONGTEXT NULL');
                } catch (\Throwable $e) {
                }
            }
            self::$schemaChecked = true;
        } catch (\Throwable $e) {
            Log::warning('DocumentSignature::ensureSchema warning: ' . $e->getMessage());
        }
    }

    public function getSigningUrlAttribute(): ?string
    {
        if (empty($this->external_signature_id)) {
            return null;
        }
        return route('user.documents.sign.show', $this->external_signature_id);
    }

    public function documentHistory()
    {
        return $this->belongsTo(DocumentHistory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function signedBy()
    {
        return $this->belongsTo(Admin::class, 'signed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }

    public function scopeByProvider($query, $provider)
    {
        return $query->where('signature_provider', $provider);
    }

    public function markAsSigned($signatureData = null)
    {
        $this->update([
            'status' => 'signed',
            'signed_at' => now(),
            'signature_data' => $signatureData ?? $this->signature_data,
        ]);
    }

    public function markAsDeclined()
    {
        $this->update([
            'status' => 'declined',
        ]);
    }

    public function markAsExpired()
    {
        $this->update([
            'status' => 'expired',
        ]);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid()
    {
        return $this->status === 'signed' && !$this->isExpired();
    }
}
