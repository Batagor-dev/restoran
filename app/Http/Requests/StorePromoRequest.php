<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromoRequest extends FormRequest
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
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->has('discount_value') && $this->discount_value !== null) {
            $this->merge([
                'discount_value' => str_replace('.', '', $this->discount_value),
            ]);
        }

        if ($this->has('minimum_purchase') && $this->minimum_purchase !== null) {
            $this->merge([
                'minimum_purchase' => str_replace('.', '', $this->minimum_purchase),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scope' => 'required|in:order,product,category_product',
            'type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_per_customer' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ];
    }
}
