<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    protected $fillable = ['name', 'value', 'applies_to', 'company_id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
