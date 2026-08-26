<?php

namespace App\Models;

use App\Models\Traits\BelongsToOutlet;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockMovement extends Model
{
    use BelongsToOutlet, HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function productStock()
    {
        return $this->belongsTo(ProductStock::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
