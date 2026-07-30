<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_stock_id' => 'required|exists:product_stocks,id',
            'movement_type' => 'required|in:in,out,adjustment,return',
            'quantity' => 'required|integer|min:1',
            'reference_type' => 'nullable|string|max:255',
            'reference_id' => 'nullable|integer',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}