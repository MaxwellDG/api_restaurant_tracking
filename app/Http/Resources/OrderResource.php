<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->resource->uuid,
            'user' => new UserResource($this->resource->user),  
            'items' => $this->formatOrderItems(),
            'fees' => $this->formatFees(),
            'total' => $this->resource->total,
            'subtotal' => $this->resource->subtotal,
            'status' => $this->resource->status,
            'receipt_id' => $this->resource->receipt_id,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }

    private function formatOrderItems(): array
    {
        return $this->resource->items->map(function ($item) {
            $quantity = $item->pivot->quantity;
            $unitPrice = $item->pivot->unit_price;

            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $unitPrice * $quantity,
                'category' => $item->category_id,
                'image' => $item->image,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ];
        })->toArray();
    }

    private function formatFees(): array
    {
        return $this->resource->fees->map(function ($fee) {
            return [
                'id' => $fee->id,
                'name' => $fee->name,
                'value' => $fee->pivot->value,
            ];
        })->toArray();
    }
}