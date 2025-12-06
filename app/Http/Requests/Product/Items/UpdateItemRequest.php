<?php

namespace App\Http\Requests\Product\Items;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $item = $this->route('item');
        $itemId = $item ? $item->id : $this->route('id');
        $companyId = Auth::user()->company_id;
        
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('items', 'name')
                    ->ignore($itemId)
                    ->where('company_id', $companyId)
            ],
            'description' => 'sometimes|nullable|string|max:1000',
            'price' => 'sometimes|required|numeric|min:0|max:999999.99',
            'quantity' => 'sometimes|integer|min:0',
            'type_of_unit' => 'sometimes|string|max:255',
            'image' => 'sometimes|nullable|url',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The item name is required.',
            'name.string' => 'The item name must be a string.',
            'name.max' => 'The item name may not be greater than 255 characters.',
            'name.unique' => 'An item with this name already exists.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'price.required' => 'The price is required.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price must be at least 0.',
            'price.max' => 'The price may not be greater than 999,999.99.',
            'quantity.integer' => 'The quantity must be a valid number.',
            'quantity.min' => 'The quantity must be at least 0.',
            'type_of_unit.string' => 'The unit type must be a string.',
            'type_of_unit.max' => 'The unit type may not be greater than 255 characters.',
            'image.url' => 'The image URL must be a valid URL.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'item name',
            'description' => 'item description',
            'price' => 'item price',
            'quantity' => 'quantity',
            'type_of_unit' => 'unit type',
            'image' => 'image URL',
        ];
    }
}
