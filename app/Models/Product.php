<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'is_favorite',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function favorites()
    {
        return $this->hasMany(FavoriteProduct::class);
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    /**
     * Effective selling price at the given outlet.
     * Falls back to the global product price when no outlet override exists.
     */
    public function priceForOutlet(?int $outletId): float
    {
        if ($outletId) {
            $stock = $this->stocks->firstWhere('outlet_id', $outletId)
                ?? ProductStock::where('product_id', $this->id)->where('outlet_id', $outletId)->first();

            if ($stock && $stock->price !== null) {
                return (float) $stock->price;
            }
        }

        return (float) $this->price;
    }

    public function scopeFavorite($query)
    {
        if (auth()->check()) {
            return $query->whereHas('favorites', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        return $query;
    }

    public function getIsFavoriteAttribute()
    {
        if (! auth()->check()) {
            return false;
        }

        // Gunakan relasi yang sudah di-eager-load bila ada untuk hindari N+1
        if ($this->relationLoaded('favorites')) {
            return $this->favorites->contains('user_id', auth()->id());
        }

        return $this->favorites()->where('user_id', auth()->id())->exists();
    }
}
