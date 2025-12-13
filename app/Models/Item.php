<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends BaseModel
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'category_id', 'company_id', 'quantity', 'type_of_unit'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}