<?php

namespace App\Models;

use App\Models\Traits\BelongsToOutlet;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiningTable extends Model
{
    use HasFactory, HasUuid, BelongsToOutlet, SoftDeletes;

    protected $table = 'dining_tables';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
