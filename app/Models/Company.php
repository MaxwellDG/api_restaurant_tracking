<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id'];

    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function create(array $data)
    {
        return self::create($data);
    }

    public static function find(int $id)
    {
        return self::find($id);
    }
}