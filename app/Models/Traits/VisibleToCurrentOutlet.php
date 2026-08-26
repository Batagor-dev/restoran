<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Data kampanye (promo) yang outlet_id-nya boleh NULL (= global).
 * - NULL  : terlihat di semua outlet
 * - Terisi: hanya terlihat di outlet yang sama
 */
trait VisibleToCurrentOutlet
{
    protected static function bootVisibleToCurrentOutlet(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->outlet_id) && auth()->check()) {
                // Karyawan otomatis milik outletnya; Super Admin/Owner tanpa
                // outlet terpilih membuat promo GLOBAL (null).
                if (! auth()->user()->hasRole(['Super Admin', 'Owner'])) {
                    $model->outlet_id = auth()->user()->current_outlet_id;
                }
            }
        });

        static::addGlobalScope('outletVisibility', function (Builder $builder) {
            $user = auth()->user();

            if (! $user || ! $user->current_outlet_id) {
                return; // Super Admin/Owner tanpa outlet aktif melihat semuanya
            }

            $table = $builder->getModel()->getTable();

            $builder->where(function (Builder $q) use ($table, $user) {
                $q->whereNull($table.'.outlet_id')
                    ->orWhere($table.'.outlet_id', $user->current_outlet_id);
            });
        });
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }
}
