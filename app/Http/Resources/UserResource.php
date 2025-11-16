<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'company_id' => $this->resource->company_id,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'role' => $this->resource->role,
            'email_verified_at' => $this->resource->email_verified_at,
        ];
    }
}