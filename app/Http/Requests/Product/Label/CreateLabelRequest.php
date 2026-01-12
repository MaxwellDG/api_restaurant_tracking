<?php

namespace App\Http\Requests\Product\Label;

use App\Http\Requests\CompanyScopedRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateLabelRequest extends CompanyScopedRequest
{
    public function rules(): array
    {
        $companyId = Auth::user()->company_id;
        
        return array_merge([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('labels', 'name')->where('company_id', $companyId)
            ],
        ], ['company_id' => 'prohibited']);
    }

    public function messages(): array
    {
        return array_merge([
            'name.required' => 'The label name is required.',
            'name.string' => 'The label name must be a string.',
            'name.max' => 'The label name may not be greater than 255 characters.',
            'name.unique' => 'A label with this name already exists in your company.',
        ], $this->baseMessages());
    }
}

