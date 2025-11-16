<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'user' => new UserResource($this->resource->user),  
            'company' => new CompanyResource($this->resource->company),
            'items' => ItemResource::collection($this->resource->items),
            'total' => $this->resource->total,
            'subtotal' => $this->resource->subtotal,
            'status' => $this->resource->status,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}