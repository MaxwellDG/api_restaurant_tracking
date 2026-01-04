<?php

namespace App\Http\Requests\Product\Items;

use App\Http\Requests\CompanyScopedRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateItemRequest extends CompanyScopedRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $companyId = Auth::user()->company_id;
        
        return array_merge([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('items', 'name')->where('company_id', $companyId)
            ],
            'description' => 'nullable|string|max:1000',
            'price' => 'sometimes|numeric|min:0|max:999999.99',
            'quantity' => 'sometimes|integer|min:0',
            'type_of_unit' => 'sometimes|string|max:255',
            'image' => 'sometimes|nullable|url',
            'category_id' => 'required|exists:categories,id',
        ], ['company_id' => 'prohibited']);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge([
            'name.required' => 'The item name is required.',
            'name.string' => 'The item name must be a string.',
            'name.max' => 'The item name may not be greater than 255 characters.',
            'name.unique' => 'An item with this name already exists.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price must be at least 0.',
            'category_id.required' => 'The category is required.',
            'category_id.exists' => 'The selected category does not exist.',
            'image.url' => 'The image URL must be a valid URL.',
        ], $this->baseMessages());
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
            'category_id' => 'category',
            'image' => 'image URL',
        ];
    }
}
