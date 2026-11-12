<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Service extends Model
{
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_DRAFT = 'draft';

    protected $fillable = [
        'seller_id',
        'category_id',
        'title',
        'description',
        'thumbnail_url', // path di disk "public", mis. services/abc.jpg
        'status',        // published | draft
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

    /**
     * Jasa yang boleh tampil di katalog publik:
     * berstatus tayang dan penjualnya sudah diverifikasi admin.
     */
    public function scopeVisibleInCatalog(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereHas('seller.sellerProfile', fn ($q) => $q->where('is_verified', true));
    }

    /**
     * URL publik gambar sampul, atau null jika belum ada.
     * Pemakaian: $service->thumbnail
     */
    protected function thumbnail(): Attribute
    {
        // asset() mengikuti host yang sedang dibuka (localhost / domain Laragon),
        // tidak bergantung pada APP_URL seperti Storage::url().
        return Attribute::get(fn () => $this->thumbnail_url
            ? asset('storage/' . ltrim($this->thumbnail_url, '/'))
            : null);
    }
}
