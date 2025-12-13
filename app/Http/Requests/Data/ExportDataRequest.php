<?php

namespace App\Http\Requests\Data;

use Illuminate\Foundation\Http\FormRequest;

class ExportDataRequest extends FormRequest
{
    public function rules(): array
    {
        $dateRule = function ($attribute, $value, $fail) {
            // Accept Unix timestamp (numeric) or any valid date string
            if (!is_numeric($value) && strtotime($value) === false) {
                $fail("The $attribute must be a valid date or Unix timestamp.");
            }
        };

        return [
            'start_date' => ['required', $dateRule],
            'end_date' => ['required', $dateRule],
            'email' => 'required|email'
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.date' => 'The start date must be a valid date.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'email.email' => 'The email must be a valid email address.',
        ];
    }
}