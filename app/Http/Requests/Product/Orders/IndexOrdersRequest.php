<?php

namespace App\Http\Requests\Product\Orders;

use Illuminate\Foundation\Http\FormRequest;

class IndexOrdersRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'item_id' => 'nullable|integer|exists:items,id',
            'page' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:open,closed,paid,cancelled',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_date.date' => 'The start date must be a valid date.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'item_id.integer' => 'The item ID must be an integer.',
            'item_id.exists' => 'The selected item does not exist.',
            'page.integer' => 'The page must be an integer.',
            'page.min' => 'The page must be at least 1.',
            'status.required' => 'The status is required.',
            'status.string' => 'The status must be a string.',
            'status.in' => 'The status must be open, pending, or completed.',
        ];
    }
}
