<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'account_name',
        'account_number',
        'account_number_last4',
        'routing_number',
        'swift_code',
        'iban',
        'currency',
        'country',
        'account_type',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected $hidden = [
        'account_number',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function toSafeArray(): array
    {
        return [
            'id' => $this->id,
            'bank_name' => $this->bank_name,
            'account_name' => $this->account_name,
            'account_number_masked' => '****' . $this->account_number_last4,
            'routing_number' => $this->routing_number,
            'swift_code' => $this->swift_code,
            'iban' => $this->iban ? $this->maskIban($this->iban) : null,
            'currency' => $this->currency,
            'country' => $this->country,
            'account_type' => $this->account_type,
            'is_default' => $this->is_default,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function maskIban(string $iban): string
    {
        $length = strlen($iban);
        if ($length <= 4) {
            return $iban;
        }

        return str_repeat('*', max(0, $length - 4)) . substr($iban, -4);
    }
}
