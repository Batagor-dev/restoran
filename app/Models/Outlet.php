<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'outlet_user');
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'outlet_id');
    }
}
