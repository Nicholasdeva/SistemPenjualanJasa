<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'midtrans_order_id',
        'transaction_id',
        'payment_method',
        'payment_type',
        'snap_token',
        'amount',
        'status',
        'paid_at',
        'raw_notification',
    ];

    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'paid_at'          => 'datetime',
            'raw_notification' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}