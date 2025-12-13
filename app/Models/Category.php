<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseModel
{
    use HasFactory;

    protected $fillable = ['name', 'company_id'];

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