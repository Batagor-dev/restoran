<?php

namespace App\Models\Traits;

use App\Models\Outlet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToOutlet
{
    protected static function bootBelongsToOutlet(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->outlet_id) && auth()->check()) {
                $model->outlet_id = auth()->user()->current_outlet_id;
            }
        });

        static::addGlobalScope('outlet', function (Builder $builder) {
            if (auth()->check()) {
                $user = auth()->user();
                // Standard users / Employees are strictly scoped by their current active outlet
                if (! $user->hasRole(['Super Admin', 'Owner'])) {
                    $builder->where($builder->getModel()->getTable().'.outlet_id', $user->current_outlet_id);
                } else {
                    // Super Admin / Owner: scope only if an active outlet is selected
                    if ($user->current_outlet_id) {
                        $builder->where($builder->getModel()->getTable().'.outlet_id', $user->current_outlet_id);
                    }
                }
            }
        });
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }
}
