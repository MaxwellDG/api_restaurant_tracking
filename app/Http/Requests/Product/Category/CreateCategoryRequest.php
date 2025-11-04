<?php

namespace App\Http\Requests\Product\Category;

use App\Http\Requests\CompanyScopedRequest;

class CreateCategoryRequest extends CompanyScopedRequest
{
    public function rules(): array
    {
        return array_merge([
            'name' => 'required|string|max:255',
        ], ['company_id' => 'prohibited']);
    }

    public function messages(): array
    {
        return array_merge([
            'name.required' => 'The category name is required.',
            'name.string' => 'The category name must be a string.',
            'name.max' => 'The category name may not be greater than 255 characters.',
        ], $this->baseMessages());
    }
}