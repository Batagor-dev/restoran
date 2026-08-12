<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promo extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'discount_value' => 'decimal:2',
        'minimum_purchase' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_per_customer' => 'integer',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'promo_products');
    }

    public function categories()
    {
        return $this->belongsToMany(ProductCategory::class, 'promo_categories');
    }
}
