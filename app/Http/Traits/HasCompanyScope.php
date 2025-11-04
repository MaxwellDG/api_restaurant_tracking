<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Auth;

trait HasCompanyScope
{
    /**
     * Get the authenticated user's company_id
     */
    protected function getAuthCompanyId(): ?int
    {
        return Auth::user()?->company_id;
    }

    /**
     * Add company_id to data array from authenticated user
     */
    protected function withCompanyId(array $data): array
    {
        $data['company_id'] = $this->getAuthCompanyId();
        return $data;
    }

    /**
     * Scope query to authenticated user's company
     */
    protected function scopeToAuthCompany($query)
    {
        return $query->where('company_id', $this->getAuthCompanyId());
    }
}

