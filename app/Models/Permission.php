<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id', 'uuid'];

    public function permissiongroup()
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
