<?php

namespace App\Models;

use App\Models\Traits\BelongsToOutlet;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use BelongsToOutlet, HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'voided_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function table()
    {
        return $this->belongsTo(DiningTable::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function refundedBy()
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    /**
     * Transaksi valid (belum di-refund / di-void) — dipakai untuk laporan pendapatan.
     */
    public function scopeValid($query)
    {
        return $query->where('status_transaction', 'normal');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
