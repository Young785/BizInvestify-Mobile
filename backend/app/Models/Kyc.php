<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Kyc extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'document_front_path',
        'document_back_path',
        'selfie_path',
        'address_proof_path',
        'status',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'rejection_reason',
        'notes',
        'verification_score',
        'document_expiry_date',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'date_of_birth',
        'nationality',
        'occupation',
        'source_of_income',
        'annual_income_range',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'document_expiry_date' => 'date',
        'date_of_birth' => 'date',
        'verification_score' => 'decimal:2',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';

    const DOCUMENT_TYPES = [
        'passport' => 'Passport',
        'national_id' => 'National ID',
        'drivers_license' => 'Driver\'s License',
        'voter_id' => 'Voter ID',
    ];

    const INCOME_RANGES = [
        'under_25k' => 'Under $25,000',
        '25k_50k' => '$25,000 - $50,000',
        '50k_100k' => '$50,000 - $100,000',
        '100k_250k' => '$100,000 - $250,000',
        '250k_500k' => '$250,000 - $500,000',
        'over_500k' => 'Over $500,000',
    ];

    /**
     * Get the user that owns the KYC application.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who reviewed the KYC application.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope for pending applications.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved applications.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for rejected applications.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Check if KYC is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if KYC is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if KYC is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Approve the KYC application.
     */
    public function approve(User $reviewer, string $notes = null): bool
    {
        $updated = $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'notes' => $notes,
            'rejection_reason' => null,
        ]);

        if ($updated) {
            // Update user KYC status
            $this->user->update([
                'kyc_status' => 'verified',
                'kyc_verified_at' => now(),
                'kyc_rejected_at' => null,
                'kyc_rejection_reason' => null,
            ]);

            // Create notification
            $this->user->notifications()->create([
                'type' => 'kyc_approved',
                'title' => 'KYC Verification Approved',
                'message' => 'Your identity verification has been approved. You can now access all platform features.',
                'data' => ['kyc_id' => $this->id],
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => $this->user_id,
                'type' => 'kyc_approved',
                'description' => 'KYC verification approved by admin',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'data' => [
                    'kyc_id' => $this->id,
                    'reviewer_id' => $reviewer->id,
                    'notes' => $notes,
                ],
            ]);
        }

        return $updated;
    }

    /**
     * Reject the KYC application.
     */
    public function reject(User $reviewer, string $reason, string $notes = null): bool
    {
        $updated = $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'rejection_reason' => $reason,
            'notes' => $notes,
        ]);

        if ($updated) {
            // Update user KYC status
            $this->user->update([
                'kyc_status' => 'rejected',
                'kyc_rejected_at' => now(),
                'kyc_rejection_reason' => $reason,
                'kyc_verified_at' => null,
            ]);

            // Create notification
            $this->user->notifications()->create([
                'type' => 'kyc_rejected',
                'title' => 'KYC Verification Rejected',
                'message' => 'Your identity verification has been rejected. Please review the feedback and resubmit.',
                'data' => [
                    'kyc_id' => $this->id,
                    'reason' => $reason,
                ],
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => $this->user_id,
                'type' => 'kyc_rejected',
                'description' => 'KYC verification rejected by admin',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'data' => [
                    'kyc_id' => $this->id,
                    'reviewer_id' => $reviewer->id,
                    'reason' => $reason,
                    'notes' => $notes,
                ],
            ]);
        }

        return $updated;
    }

    /**
     * Get document URLs.
     */
    public function getDocumentUrls(): array
    {
        return [
            'document_front' => $this->document_front_path ? Storage::url($this->document_front_path) : null,
            'document_back' => $this->document_back_path ? Storage::url($this->document_back_path) : null,
            'selfie' => $this->selfie_path ? Storage::url($this->selfie_path) : null,
            'address_proof' => $this->address_proof_path ? Storage::url($this->address_proof_path) : null,
        ];
    }

    /**
     * Calculate verification score based on document quality and completeness.
     */
    public function calculateVerificationScore(): float
    {
        $score = 0;
        $maxScore = 100;

        // Document completeness (40 points)
        if ($this->document_front_path) $score += 15;
        if ($this->document_back_path) $score += 10;
        if ($this->selfie_path) $score += 10;
        if ($this->address_proof_path) $score += 5;

        // Personal information completeness (30 points)
        if ($this->date_of_birth) $score += 5;
        if ($this->nationality) $score += 5;
        if ($this->occupation) $score += 5;
        if ($this->source_of_income) $score += 5;
        if ($this->annual_income_range) $score += 5;
        if ($this->address_line_1 && $this->city && $this->country) $score += 5;

        // Document validity (30 points)
        if ($this->document_expiry_date && $this->document_expiry_date->isFuture()) {
            $score += 30;
        } elseif ($this->document_expiry_date) {
            $score += 15; // Partial points for expired documents
        }

        return round(($score / $maxScore) * 100, 2);
    }
}