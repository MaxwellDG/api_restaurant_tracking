<?php

namespace App\Http\Requests\Product\Orders;

use App\Http\Requests\CompanyScopedRequest;

class CreateOrderRequest extends CompanyScopedRequest
{
    public function rules(): array
    {
        return array_merge([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ], ['company_id' => 'prohibited']);
    }

    public function messages(): array
    {
        return array_merge([
            'items.required' => 'The items are required.',
            'items.array' => 'The items must be an array.',
        ], $this->baseMessages());
    }
}