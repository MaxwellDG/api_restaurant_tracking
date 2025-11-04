<?php

namespace App\Http\Requests;

use App\Http\Traits\HasCompanyScope;
use Illuminate\Foundation\Http\FormRequest;

abstract class CompanyScopedRequest extends FormRequest
{
    use HasCompanyScope;

    /**
     * Get validated data with company_id automatically injected
     */
    public function validatedWithCompany(): array
    {
        return $this->withCompanyId($this->validated());
    }

    /**
     * Override rules to always prohibit company_id in requests
     */
    abstract public function rules(): array;

    /**
     * Merge company_id prohibition into rules
     */
    protected function rulesWithCompanyProhibition(): array
    {
        return array_merge($this->rules(), [
            'company_id' => 'prohibited',
        ]);
    }

    /**
     * Get base error messages
     */
    protected function baseMessages(): array
    {
        return [
            'company_id.prohibited' => 'You cannot manually set the company_id. It is automatically assigned from your authenticated user.',
        ];
    }
}

