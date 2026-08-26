<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        foreach (['price', 'cost_price'] as $field) {
            if ($this->has($field) && $this->$field !== null) {
                $this->merge([
                    $field => str_replace('.', '', $this->$field),
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ];
    }
}
