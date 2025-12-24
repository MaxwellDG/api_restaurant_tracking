<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Category extends BaseModel
{
    use HasFactory;

    protected $fillable = ['name', 'company_id'];
    
    protected static function booted()
    {
        static::addGlobalScope('company', function ($query) {
            if (Auth::check()) {
                $query->where('company_id', Auth::user()->company_id);
            }
        });
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function getCategories()
    {
        return self::all();
    }
}