<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceMessage extends Model
{
    public const SYSTEM_PREFIX = '[SISTEM] ';

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

    public function isSystemMessage(): bool
    {
        return str_starts_with((string) $this->message, self::SYSTEM_PREFIX);
    }

    public function displayMessage(): string
    {
        $text = (string) ($this->message ?? '');

        if ($this->isSystemMessage()) {
            return substr($text, strlen(self::SYSTEM_PREFIX));
        }

        return $text;
    }

    public function attachmentName(): ?string
    {
        if (!$this->attachment_url) {
            return null;
        }

        $base = basename($this->attachment_url);

        // Hapus prefix timestamp unik (mis. 1763541234_namafile.zip -> namafile.zip) untuk tampilan
        return preg_replace('/^\d+_/', '', $base) ?: $base;
    }
}