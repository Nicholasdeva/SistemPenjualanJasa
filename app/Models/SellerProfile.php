<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'ktp_number',
        'bank_name',
        'bank_account',
        'wallet_balance',
        'escrow_balance',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'wallet_balance' => 'decimal:2',
            'escrow_balance' => 'decimal:2',
            'is_verified'    => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}