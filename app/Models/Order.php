<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'invoice_no',
        'buyer_id',
        'service_package_id',
        'status',
        'project_brief',
        'total_price',
        'platform_fee',
        'deadline_at',
    ];

    protected function casts(): array
    {
        return [
            'total_price'  => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'deadline_at'  => 'datetime',
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
}