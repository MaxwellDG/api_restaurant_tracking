<?php

namespace App\Http\Requests\Product\Items;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemQuantityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'quantity' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'The quantity is required.',
            'quantity.numeric' => 'The quantity must be a number.',
        ];
    }
}

