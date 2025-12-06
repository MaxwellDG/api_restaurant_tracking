<?php

namespace App\Http\Requests\Product\Fees;

use App\Http\Requests\CompanyScopedRequest;

class CreateFeeRequest extends CompanyScopedRequest
{
    public function rules(): array
    {
        return array_merge([
            'name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'applies_to' => 'required|string|in:order,item',
        ], ['company_id' => 'prohibited']);
    }

    public function messages(): array
    {
        return array_merge([
            'name.required' => 'The fee name is required.',
            'name.string' => 'The fee name must be a string.',
            'name.max' => 'The fee name may not be greater than 255 characters.',
            'value.required' => 'The fee value is required.',
            'value.numeric' => 'The fee value must be a number.',
            'value.min' => 'The fee value must be at least 0.',
            'applies_to.required' => 'The applies_to field is required.',
            'applies_to.string' => 'The applies_to field must be a string.',
            'applies_to.in' => 'The applies_to field must be either "order" or "item".',
        ], $this->baseMessages());
    }
}
