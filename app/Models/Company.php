<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'user_id'];

    protected static function boot()
    {
        parent::boot();

        // Automatically create a fee entry when a company is created
        static::created(function ($company) {
            Fee::create([
                'company_id' => $company->id,
                'name' => 'Tax',
                'value' => 0,
                'applies_to' => 'order'
            ]);
        });
    }

    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }
}