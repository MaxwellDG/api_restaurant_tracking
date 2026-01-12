<?php

namespace App\Http\Requests\Product\Label;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateLabelRequest extends FormRequest
{
    public function rules(): array
    {
        $companyId = Auth::user()->company_id;
        $labelId = $this->route('label')->id;
        
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('labels', 'name')
                    ->where('company_id', $companyId)
                    ->ignore($labelId)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The label name is required.',
            'name.string' => 'The label name must be a string.',
            'name.max' => 'The label name may not be greater than 255 characters.',
            'name.unique' => 'A label with this name already exists in your company.',
        ];
    }
}

