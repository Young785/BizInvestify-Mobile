<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'date_of_birth',
        'bio',
        'avatar_url',
        'kyc_status',
        'kyc_documents',
        'trust_score',
        'is_verified',
        'business_name',
        'business_type',
        'investment_amount',
        'investment_focus',
        'phone_verified_at',
        'two_factor_confirmed_at',
        'remember_device',
        'trusted_devices',
        'email_verification_token',
        'email_verification_expires_at',
        'phone_verification_token',
        'phone_verification_expires_at',
        'email_verified_at',
        'password_reset_token',
        'password_reset_expires_at',
        'email_2fa_code',
        'email_2fa_expires_at',
        'email_2fa_enabled',
        'two_factor_secret',
        'two_factor_skipped',
        'kyc_verified_at',
        'kyc_rejected_at',
        'kyc_rejection_reason',
        'stripe_customer_id',
        'stripe_account_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'email_verification_token',
        'phone_verification_token',
        'password_reset_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'email_verification_expires_at' => 'datetime',
            'phone_verification_expires_at' => 'datetime',
            'password_reset_expires_at' => 'datetime',
            'email_2fa_expires_at' => 'datetime',
            'email_2fa_enabled' => 'boolean',
            'two_factor_skipped' => 'boolean',
            'password' => 'hashed',
            'kyc_documents' => 'array',
            'trusted_devices' => 'array',
            'trust_score' => 'decimal:2',
            'is_verified' => 'boolean',
            'remember_device' => 'boolean',
            'kyc_verified_at' => 'datetime',
            'kyc_rejected_at' => 'datetime',
            'date_of_birth' => 'date',
        ];
    }

    /**
     * Get the products that the user has listed.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    /**
     * Get the businesses that the user has listed.
     */
    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class, 'seller_id');
    }

    /**
     * Get the investments that the user has made.
     */
    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class, 'investor_id');
    }

    /**
     * Get the messages sent by the user.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get the messages received by the user.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Get the transactions where the user is the buyer.
     */
    public function buyerTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    }

    /**
     * Get the transactions where the user is the seller.
     */
    public function sellerTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'seller_id');
    }

    /**
     * Check if the user is a seller.
     */
    public function isSeller(): bool
    {
        return $this->role === 'seller';
    }

    /**
     * Check if the user is a buyer/investor.
     */
    public function isBuyer(): bool
    {
        return $this->role === 'buyer';
    }

    /**
     * Check if the user is an investor (alias for isBuyer).
     */
    public function isInvestor(): bool
    {
        return $this->isBuyer();
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user's KYC is verified.
     */
    public function isKycVerified(): bool
    {
        return $this->kyc_status === 'verified';
    }

    /**
     * Check if the user has submitted KYC documents (pending or verified).
     */
    public function hasSubmittedKyc(): bool
    {
        return in_array($this->kyc_status, ['pending', 'verified']);
    }

    /**
     * Check if the user's email is verified.
     */
    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Check if the user's phone is verified.
     */
    public function isPhoneVerified(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    /**
     * Check if the user has enabled two-factor authentication.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Check if the user is fully verified (email, phone, KYC, and 2FA).
     */
    public function isFullyVerified(): bool
    {
        return $this->isEmailVerified() 
            && $this->isPhoneVerified() 
            && $this->hasSubmittedKyc() 
            && ($this->hasTwoFactorEnabled() || $this->two_factor_skipped);
    }

    /**
     * Check if the user has completed all required verification steps for dashboard access.
     */
    public function hasCompletedRequiredSteps(): bool
    {
        return $this->isEmailVerified() 
            && $this->isPhoneVerified() 
            && $this->hasSubmittedKyc() 
            && ($this->hasTwoFactorEnabled() || $this->two_factor_skipped);
    }

    /**
     * Get the total earnings from completed transactions.
     */
    public function getTotalEarningsAttribute()
    {
        return $this->sellerTransactions()
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Get the total amount invested.
     */
    public function getTotalInvestedAttribute()
    {
        return $this->investments()
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Get verification progress as percentage.
     */
    public function getVerificationProgressAttribute(): int
    {
        $steps = 0;
        $completed = 0;

        // Email verification (required)
        $steps++;
        if ($this->isEmailVerified()) {
            $completed++;
        }

        // Phone verification (required)
        $steps++;
        if ($this->isPhoneVerified()) {
            $completed++;
        }

        // KYC verification (required)
        $steps++;
        if ($this->hasSubmittedKyc()) {
            $completed++;
        }

        // Two-factor authentication (required)
        $steps++;
        if ($this->hasTwoFactorEnabled()) {
            $completed++;
        }

        return $steps > 0 ? (int) (($completed / $steps) * 100) : 0;
    }

    /**
     * Generate and set email verification token.
     */
    public function generateEmailVerificationToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update([
            'email_verification_token' => $token,
            'email_verification_expires_at' => now()->addHours(24),
        ]);
        return $token;
    }

    /**
     * Generate and set phone verification token.
     */
    public function generatePhoneVerificationToken(): string
    {
        $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->update([
            'phone_verification_token' => $token,
            'phone_verification_expires_at' => now()->addMinutes(10),
        ]);
        return $token;
    }

    /**
     * Verify email with token.
     */
    public function verifyEmail(string $token): bool
    {
        if ($this->email_verification_token === $token && 
            $this->email_verification_expires_at && 
            $this->email_verification_expires_at->isFuture()) {
            
            $this->update([
                'email_verified_at' => now(),
                'email_verification_token' => null,
                'email_verification_expires_at' => null,
            ]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Verify phone with token.
     */
    public function verifyPhone(string $token): bool
    {
        if ($this->phone_verification_token === $token && 
            $this->phone_verification_expires_at && 
            $this->phone_verification_expires_at->isFuture()) {
            
            $this->update([
                'phone_verified_at' => now(),
                'phone_verification_token' => null,
                'phone_verification_expires_at' => null,
            ]);
            
            return true;
        }
        
        return false;
    }


}
