<?php

namespace App\Http\Requests\Product\Category;

use App\Http\Requests\CompanyScopedRequest;
use App\Models\Category;

class CreateCategoryRequest extends CompanyScopedRequest
{
    public function rules(): array
    {
        return array_merge([
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = Category::whereRaw('LOWER(name) = ?', [strtolower($value)])
                        ->where('company_id', auth()->user()->company_id)
                        ->exists();

                    if ($exists) {
                        $fail('A category with this name already exists in your company.');
                    }
                },
            ],
        ], ['company_id' => 'prohibited']);
    }

    public function messages(): array
    {
        return array_merge([
            'name.required' => 'The category name is required.',
            'name.string' => 'The category name must be a string.',
            'name.max' => 'The category name may not be greater than 255 characters.',
            'name.unique' => 'A category with this name already exists in your company.',
        ], $this->baseMessages());
    }
}