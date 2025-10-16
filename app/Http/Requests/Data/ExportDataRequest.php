<?php

namespace App\Http\Requests\Data;

use Illuminate\Foundation\Http\FormRequest;

class ExportDataRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
            'address' => 'email'
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.date' => 'The start date must be a valid date.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'address.email' => 'The address must be a valid email address.',
        ];
    }
}