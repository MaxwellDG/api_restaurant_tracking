<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /**
     * Prepare a date for array / JSON serialization.
     * Uses ISO 8601 format for consistency across the API.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('c');
    }
}
