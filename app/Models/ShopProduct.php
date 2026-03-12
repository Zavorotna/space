<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ShopProduct extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'title', 'description', 'price_coins', 'price_uah',
        'accept_coins', 'accept_card', 'stock', 'is_active',
    ];

    protected function casts(): array
    {
        return ['accept_coins' => 'boolean', 'accept_card' => 'boolean', 'is_active' => 'boolean'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos');
    }

    public function orders() { return $this->hasMany(ShopOrder::class, 'product_id'); }
    public function scopeAvailable($q) { return $q->where('is_active', true)->where('stock', '>', 0); }
}
