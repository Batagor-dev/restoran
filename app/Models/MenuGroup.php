<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuGroup extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'name', 'sort', 'status', 'permission_group_id',
    ];

    public function menus()
    {
        return $this->hasMany(Menu::class, 'menu_group_id')->orderBy('sort');
    }

    public function permissionGroup()
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
