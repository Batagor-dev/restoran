<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->filled('price')) {
            $this->merge([
                'price' => str_replace('.', '', $this->input('price')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'quantity' => 'required|integer|min:0',
            'price' => 'nullable|numeric|min:0',
        ];
    }
}
