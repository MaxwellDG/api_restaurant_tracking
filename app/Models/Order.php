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
    protected $fillable = ['user_id', 'total', 'completed_at', 'company_id', 'subtotal', 'status', 'receipt_id', 'label'];
    
    protected $casts = [
        'completed_at' => 'datetime',
    ];

    protected static function booted()
    {
        // Restore inventory when an order is deleted
        static::deleting(function ($order) {
            // Load items with their pivot data
            $order->load('items');
            
            // Restore inventory for each item in the order
            foreach ($order->items as $item) {
                $item->increment('quantity', $item->pivot->quantity);
            }
        });
    }

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
                    ->withPivot('id', 'quantity', 'unit_price')
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
     * Validate and prepare order items data
     * 
     * @return array ['items' => Collection, 'itemsData' => Collection]
     */
    private static function validateAndPrepareItems(int $companyId, array $itemsData)
    {
        $itemsData = collect($itemsData)->keyBy('id');
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

        return ['items' => $items, 'itemsData' => $itemsData];
    }

    /**
     * Calculate subtotal from items
     */
    private static function calculateSubtotal($items, $itemsData)
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $unitPrice = $item->price;
            $payloadQuantity = $itemsData[$item->id]['quantity'];
            $subtotal += $unitPrice * $payloadQuantity;
        }
        return $subtotal;
    }

    /**
     * Calculate total from subtotal and fees
     */
    private static function calculateTotal(float $subtotal, array $calculatedFees)
    {
        $total = $subtotal;
        foreach ($calculatedFees as $fee) {
            $total += $fee['value'];
        }
        return $total;
    }

    /**
     * Create a new order with items
     */
    public static function createWithItems(int $user_id, array $data)
    {
        $companyId = $data['company_id'];
        $label = $data['label'] ?? null;
        
        // Validate and prepare items
        $prepared = self::validateAndPrepareItems($companyId, $data['items']);
        $items = $prepared['items'];
        $itemsData = $prepared['itemsData'];
        
        return \Illuminate\Support\Facades\DB::transaction(function () use ($user_id, $companyId, $label, $items, $itemsData) {
            $order = new static();
            $order->uuid = (string) \Illuminate\Support\Str::uuid();
            $order->user_id = $user_id;
            $order->company_id = $companyId;
            $order->label = $label;
            $order->status = 'open';
            $order->total = 0;
            $order->subtotal = 0;
            
            $order->save();
            
            // Calculate subtotal and attach items
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
            $total = self::calculateTotal($subtotal, $calculatedFees);
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

    /**
     * Add items to an existing order
     * 
     * @param array $itemsToAdd Array of ['id' => item_id, 'quantity' => qty]
     */
    public function addItems(array $itemsToAdd)
    {
        // Check if order is open
        if ($this->status !== 'open') {
            throw new \Exception("Cannot add items to an order with status '{$this->status}'. Order must be open.");
        }
        
        return \Illuminate\Support\Facades\DB::transaction(function () use ($itemsToAdd) {
            // Validate and prepare new items
            $prepared = self::validateAndPrepareItems($this->company_id, $itemsToAdd);
            $newItems = $prepared['items'];
            $newItemsData = $prepared['itemsData'];

            // Add or update items
            foreach ($newItems as $item) {
                $unitPrice = $item->price;
                $payloadQuantity = $newItemsData[$item->id]['quantity'];
                
                // Check if item already exists in order
                $existingItem = $this->items()->where('item_id', $item->id)->first();
                
                if ($existingItem) {
                    // Update existing item quantity
                    $newQuantity = $existingItem->pivot->quantity + $payloadQuantity;
                    $this->items()->updateExistingPivot($item->id, [
                        'quantity' => $newQuantity,
                        'unit_price' => $unitPrice
                    ]);
                } else {
                    // Attach new item
                    $this->items()->attach($item->id, [
                        'quantity' => $payloadQuantity,
                        'unit_price' => $unitPrice
                    ]);
                }
                
                // Decrement inventory
                $item->decrement('quantity', $payloadQuantity);
            }

            // Recalculate subtotal from all items
            $this->refresh();
            $subtotal = 0;
            foreach ($this->items as $item) {
                $subtotal += $item->pivot->unit_price * $item->pivot->quantity;
            }
            $this->subtotal = $subtotal;

            // Recalculate fees
            $fees = Company::find($this->company_id)->fees;
            $calculatedFees = self::calculateFees($subtotal, $fees);

            // Update fees
            $this->fees()->detach();
            foreach ($calculatedFees as $fee) {
                $this->fees()->attach($fee['fee_id'], ['value' => $fee['value']]);
            }

            // Recalculate total
            $this->total = self::calculateTotal($subtotal, $calculatedFees);
            $this->save();

            return $this->load('user', 'items', 'fees');
        });
    }

    /**
     * Remove items from an existing order using order_item_id
     * 
     * @param array $orderItemIds Array of order_item (pivot) IDs to remove
     */
    public function removeItems(array $orderItemIds)
    {
        // Check if order is open
        if ($this->status !== 'open') {
            throw new \Exception("Cannot remove items from an order with status '{$this->status}'. Order must be open.");
        }
        
        return \Illuminate\Support\Facades\DB::transaction(function () use ($orderItemIds) {
            foreach ($orderItemIds as $orderItemId) {
                // Get the order_item pivot record
                $orderItem = \Illuminate\Support\Facades\DB::table('orders_items')
                    ->where('id', $orderItemId)
                    ->where('order_id', $this->uuid)
                    ->first();
                
                if (!$orderItem) {
                    throw new \Exception("Order item with ID {$orderItemId} not found in this order.");
                }
                
                // Get the item and restore inventory
                $item = Item::find($orderItem->item_id);
                if ($item) {
                    $item->increment('quantity', $orderItem->quantity);
                }
                
                // Delete the order_item pivot record
                \Illuminate\Support\Facades\DB::table('orders_items')
                    ->where('id', $orderItemId)
                    ->delete();
            }

            // Recalculate subtotal from remaining items
            $this->refresh();
            $subtotal = 0;
            foreach ($this->items as $item) {
                $subtotal += $item->pivot->unit_price * $item->pivot->quantity;
            }
            $this->subtotal = $subtotal;

            // Recalculate fees
            $fees = Company::find($this->company_id)->fees;
            $calculatedFees = self::calculateFees($subtotal, $fees);

            // Update fees
            $this->fees()->detach();
            foreach ($calculatedFees as $fee) {
                $this->fees()->attach($fee['fee_id'], ['value' => $fee['value']]);
            }

            // Recalculate total
            $this->total = self::calculateTotal($subtotal, $calculatedFees);
            $this->save();

            return $this->load('user', 'items', 'fees');
        });
    }
}