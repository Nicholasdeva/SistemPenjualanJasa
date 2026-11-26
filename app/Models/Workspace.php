<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Workspace extends Model
{
    protected $fillable = ['order_id'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}