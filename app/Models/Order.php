<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['user_id', 'total', 'completed_at', 'company_id', 'subtotal', 'status', 'receipt_id'];
    
    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'orders_items', 'order_id', 'item_id')
                    ->withPivot('quantity', 'unit_price')
                    ->withTimestamps();
    }

    public function fees()
    {
        return $this->belongsToMany(Fee::class, 'orders_fees', 'order_id', 'fee_id')
                    ->withPivot('value')
                    ->withTimestamps();
    }

    /* 
        todo: this function should be expanded upon when "applies_to" is expanded upon. Will 
        also be adding different types of modifiers (mul, sub, sum, div etc...)
    */
    private static function calculateFees(float $subtotal, iterable $fees)
    {
        $totalFees = [];
        foreach ($fees as $fee) {
            if($fee->applies_to === 'order') {
                $tax = round($subtotal * $fee->value / 100, 2);
                $totalFees[] = [
                    'fee_id' => $fee->id,
                    'value' => $tax
                ];
            }
        }
        return $totalFees;
    }



    /**
     * Create a new order with items
     */
    public static function createWithItems(int $user_id, array $data)
    {
        $companyId = $data['company_id'];
        $itemsData = collect($data['items'])->keyBy('id');
        $itemIds = $itemsData->pluck("id")->toArray();
        $items = Item::whereIn('id', $itemIds)->get();

        // Step 1: Validate existence of all items
        $existingItems = $items->pluck('id')->toArray();
        $missingItems = array_diff($itemIds, $existingItems);
        if (!empty($missingItems)) {
            throw new \Exception('Items not found: ' . implode(', ', $missingItems));
        }

        foreach ($items as $item) {
            // Step 2: Ensure each item requested is part of this company
            if ($item->company_id !== $companyId) {
                throw new \Exception("Item '{$item->name}' (ID: {$item->id}) does not belong to this company.");
            }

            // Step 3: Check if requested quantity exceeds available quantity
            $payloadQuantity = $itemsData[$item->id]['quantity'];
            if ($item->quantity - $payloadQuantity < 0) {
                throw new \Exception("Insufficient quantity for '{$item->name}'. Available: {$item->quantity}");
            }
        }
        
        return \Illuminate\Support\Facades\DB::transaction(function () use ($user_id, $companyId, $items, $itemsData) {
            $order = new static();
            $order->uuid = (string) \Illuminate\Support\Str::uuid();
            $order->user_id = $user_id;
            $order->company_id = $companyId;
            $order->status = 'open';
            $order->total = 0;
            $order->subtotal = 0;
            
            $order->save();
            
            // Calculate subtotal
            $subtotal = 0;
            foreach ($items as $item) {
                $unitPrice = $item->price;
                $payloadQuantity = $itemsData[$item->id]['quantity'];
                
                $order->items()->attach($item->id, [
                    'quantity' => $payloadQuantity,
                    'unit_price' => $unitPrice
                ]);
                
                $item->decrement('quantity', $payloadQuantity);
                
                $subtotal += $unitPrice * $payloadQuantity;
            }
            $order->subtotal = $subtotal;

            // Calculate fees
            $fees = Company::find($companyId)->fees;
            $calculatedFees = self::calculateFees($subtotal, $fees);

            // Calculate total
            $total = $subtotal;
            foreach ($calculatedFees as $fee) {
                $total += $fee['value'];
            }
            $order->total = $total;
            $order->save();

            // Attach fees to order
            foreach ($calculatedFees as $fee) {
                $order->fees()->attach($fee['fee_id'], ['value' => $fee['value']]);
            }

            return $order->load('user', 'items', 'fees');
        });
    }

    /**
     * Update an order with items
     */
    public function updateOrder(array $data)
    {
        if (isset($data['total'])) {
            $this->total = $data['total'];
        }
        
        if (isset($data['items'])) {
            $this->items()->sync($data['items']);
        }
        
        if (isset($data['completed_at'])) {
            $this->completed_at = $data['completed_at'];
        }
        
        if (isset($data['status'])) {
            $this->status = $data['status'];
        }

        if (array_key_exists('receipt_id', $data)) {
            $this->receipt_id = $data['receipt_id'];
            $this->status = $data['receipt_id'] !== null ? 'completed' : 'pending';
            $this->completed_at = $data['receipt_id'] !== null ? now() : null;
        }
        
        $this->save();

        return $this->load('user', 'items');
    }
}