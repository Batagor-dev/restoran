<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasUuid, SoftDeletes;

    protected $guarded = ['id', 'uuid'];

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
