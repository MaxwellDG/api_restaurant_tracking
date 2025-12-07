<?php

namespace App\Http\Requests\Product\Orders;

use App\Http\Requests\CompanyScopedRequest;

class CreateOrderRequest extends CompanyScopedRequest
{
    public function rules(): array
    {
        return array_merge([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ], ['company_id' => 'prohibited']);
    }

    public function messages(): array
    {
        return array_merge([
            'items.required' => 'The items are required.',
            'items.array' => 'The items must be an array.',
            'items.*.id.required' => 'Each item must have an item_id.',
            'items.*.id.exists' => 'The selected item does not exist.',
            'items.*.quantity.required' => 'Each item must have a quantity.',
            'items.*.quantity.integer' => 'The quantity must be an integer.',
            'items.*.quantity.min' => 'The quantity must be at least 1.',
        ], $this->baseMessages());
    }
}