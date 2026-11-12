<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceMessage extends Model
{
    protected $fillable = [
        'order_id',
        'sender_id',
        'message',
        'attachment_url',
        'is_delivery',
    ];

    protected function casts(): array
    {
        return [
            'is_delivery' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}