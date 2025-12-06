<?php

namespace App\Http\Requests\Product\Fees;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'value' => 'sometimes|numeric|min:0',
            'applies_to' => 'sometimes|string|in:order,item',
            'company_id' => 'prohibited',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'The fee name must be a string.',
            'name.max' => 'The fee name may not be greater than 255 characters.',
            'value.numeric' => 'The fee value must be a number.',
            'value.min' => 'The fee value must be at least 0.',
            'applies_to.string' => 'The applies_to field must be a string.',
            'applies_to.in' => 'The applies_to field must be either "order" or "item".',
            'company_id.prohibited' => 'You cannot manually change the company_id.',
        ];
    }
}
