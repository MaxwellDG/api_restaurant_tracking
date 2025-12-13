<?php

namespace App\Models;

class Fee extends BaseModel
{
    protected $fillable = ['name', 'value', 'applies_to', 'company_id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
