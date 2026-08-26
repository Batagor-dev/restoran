<?php

namespace App\Http\Requests;

use App\Models\Outlet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiningTableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $outletId = $this->outlet_id ?: auth()->user()->current_outlet_id;
        if (! $outletId) {
            $firstOutlet = Outlet::where('status', true)->first() ?: Outlet::first();
            $outletId = $firstOutlet?->id;
        }

        return [
            'number_table' => [
                'required',
                'string',
                'max:100',
                Rule::unique('dining_tables', 'number_table')
                    ->where(function ($query) use ($outletId) {
                        return $query->where('outlet_id', $outletId)->whereNull('deleted_at');
                    }),
            ],
            'outlet_id' => 'nullable|exists:outlets,id',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'number_table.unique' => 'Nomor / nama meja sudah digunakan di outlet ini.',
        ];
    }
}
