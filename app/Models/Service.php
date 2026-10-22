<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Service extends Model
{
    protected $fillable = [
        'seller_id',
        'category_id',
        'title',
        'description',
        'status', // published | draft
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ServicePackage::class);
    }

    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(Order::class, ServicePackage::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}