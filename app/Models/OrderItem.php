<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
        'ready_at' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function cookedBy()
    {
        return $this->belongsTo(User::class, 'cooked_by');
    }

    // Scope untuk item yang belum siap
    public function scopeNotReady($query)
    {
        return $query->where('kitchen_status', '!=', 'ready');
    }

    // Scope untuk item yang sedang dimasak
    public function scopeCooking($query)
    {
        return $query->where('kitchen_status', 'cooking');
    }
}