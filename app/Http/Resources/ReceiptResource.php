<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'order' => new OrderResource($this->resource->order),
            'user' => new UserResource($this->resource->user),
            'subtotal' => $this->resource->subtotal,
            'tax' => $this->resource->tax,
            'total' => $this->resource->total,
            'payment_method' => $this->resource->payment_method,
            'status' => $this->resource->status,
            'transaction_id' => $this->resource->transaction_id,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}