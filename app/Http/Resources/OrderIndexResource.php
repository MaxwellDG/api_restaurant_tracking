<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderIndexResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->resource->uuid,
            'user' => new UserResource($this->resource->user),
            'subtotal' => $this->resource->subtotal,
            'total' => $this->resource->total,
            'status' => $this->resource->status,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
