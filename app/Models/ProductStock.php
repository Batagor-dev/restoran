<?php

namespace App\Models;

use App\Models\Traits\BelongsToOutlet;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductStock extends Model
{
    use BelongsToOutlet, HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
