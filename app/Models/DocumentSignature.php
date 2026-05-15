<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentSignature extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'signed_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

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
