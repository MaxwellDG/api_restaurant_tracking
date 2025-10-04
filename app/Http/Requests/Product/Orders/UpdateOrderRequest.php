<?php

namespace App\Http\Requests\Product\Orders;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'items' => 'array',
            'items.*.item_id' => 'exists:items,id',
            'items.*.quantity' => 'integer|min:1',
            'items.*.unit_price' => 'numeric|min:0',
            'total_amount' => 'numeric|min:0',
            'completed_at' => 'date',
            'paid_at' => 'date',
        ];
    }

    public function messages(): array
    {
        return [
            'items.array' => 'The items must be an array.',
            'items.*.item_id.exists' => 'The selected item does not exist.',
            'items.*.quantity.integer' => 'The quantity must be an integer.',
            'items.*.quantity.min' => 'The quantity must be at least 1.',
            'items.*.unit_price.numeric' => 'The unit price must be a number.',
            'items.*.unit_price.min' => 'The unit price must be at least 0.',
            'total_amount.numeric' => 'The total amount must be a number.',
            'total_amount.min' => 'The total amount must be at least 0.',
            'completed_at.date' => 'The completed at must be a date.',
            'paid_at.date' => 'The paid at must be a date.',
        ];
    }
}