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
        if (auth()->check()) {
            return $this->favorites()->where('user_id', auth()->id())->exists();
        }
        return false;
    }
}
