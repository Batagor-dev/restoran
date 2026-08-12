<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasRoles, HasUuid, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'google_id',
        'foto',
        'email',
        'gender',
        'phone',
        'address',
        'password',
        'banned_at',
        'current_outlet_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'banned_at' => 'datetime',
        ];
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'user_id');
    }

    public function outlets()
    {
        return $this->belongsToMany(Outlet::class, 'outlet_user');
    }

    public function currentOutlet()
    {
        return $this->belongsTo(Outlet::class, 'current_outlet_id');
    }

    public function favoriteProducts()
{
    return $this->belongsToMany(Product::class, 'favorite_products')
        ->withTimestamps();
}
}
