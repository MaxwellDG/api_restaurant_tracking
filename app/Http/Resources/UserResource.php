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
            'company' => new CompanyResource($this->resource->company),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'is_admin' => $this->resource->is_admin,
            'email_verified_at' => $this->resource->email_verified_at,
        ];
    }
}