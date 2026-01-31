<?php

namespace App\Http\Requests\Product\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($category) {
                    $exists = Category::whereRaw('LOWER(name) = ?', [strtolower($value)])
                        ->where('company_id', $category->company_id)
                        ->where('id', '!=', $category->id)
                        ->exists();

                    if ($exists) {
                        $fail('A category with this name already exists in your company.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.string' => 'The category name must be a string.',
            'name.max' => 'The category name may not be greater than 255 characters.',
            'name.unique' => 'A category with this name already exists in your company.',
        ];
    }
}
