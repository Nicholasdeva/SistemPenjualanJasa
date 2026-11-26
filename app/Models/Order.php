<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** Status pesanan yang sudah dapat membuka ruang kerja (workspace). */
    public const WORKSPACE_STATUSES = [
        'paid',
        'in_progress',
        'delivered',
        'revision',
        'completed',
        'disputed',
    ];

    /** Status pesanan di mana workspace bersifat baca-saja (read-only). */
    public const READ_ONLY_STATUSES = [
        'pending_payment',
        'pending',
        'completed',
        'disputed',
        'cancelled',
    ];

    protected $fillable = [
        'invoice_no',
        'buyer_id',
        'service_package_id',
        'status',
        'revision_count',
        'project_brief',
        'total_price',
        'platform_fee',
        'deadline_at',
        'seller_id', // Pastikan seller_id juga masuk fillable jika belum ada
    ];

    protected function casts(): array
    {
        return [
            'total_price'    => 'decimal:2',
            'platform_fee'   => 'decimal:2',
            'deadline_at'    => 'datetime',
            'revision_count' => 'integer',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function servicePackage(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function workspace(): HasOne
    {
        return $this->hasOne(Workspace::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WorkspaceMessage::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Ambil user penjual sebenarnya melalui relasi paket -> jasa -> penjual.
     */
    public function resolvedSeller(): ?User
    {
        return $this->servicePackage?->service?->seller;
    }

    /**
     * Batas kuota revisi dari paket yang dipesan.
     */
    public function revisionLimit(): int
    {
        return max(0, (int) ($this->servicePackage?->revision_limit ?? 0));
    }

    /**
     * Sisa kuota revisi yang masih tersedia.
     */
    public function remainingRevisions(): int
    {
        return max(0, $this->revisionLimit() - (int) $this->revision_count);
    }

    /**
     * Apakah workspace berada dalam mode baca-saja.
     */
    public function isWorkspaceReadOnly(): bool
    {
        return in_array($this->status, self::READ_ONLY_STATUSES, true);
    }

    /**
     * Apakah pesanan sudah melewati batas waktu pengerjaan.
     */
    public function isOverdue(): bool
    {
        return $this->deadline_at !== null
            && $this->deadline_at->isPast()
            && in_array($this->status, ['paid', 'in_progress', 'delivered', 'revision'], true);
    }
}