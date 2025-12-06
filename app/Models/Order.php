<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'uuid';
    protected $fillable = ['user_id', 'total', 'completed_at', 'company_id', 'subtotal', 'status'];

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

    /**
     * Create a new order with items
     */
    public static function createWithItems(int $user_id, array $data)
    {
        $order = new static();
        $order->user_id = $user_id;
        $order->company_id = $data['company_id'] ?? null;
        $order->status = 'open';
        $order->total = 0; // Initialize with 0
        $order->subtotal = 0; // Initialize with 0
        
        // Calculate total amount
        $totalAmount = 0;
        
        $order->save();

        // Attach items if provided
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                // Fetch the item to get the current price
                $item = Item::find($itemData['item_id']);
                $unitPrice = $item->price;
                $quantity = $itemData['quantity'];
                
                $order->items()->attach($itemData['item_id'], [
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice
                ]);
                
                $totalAmount += $unitPrice * $quantity;
            }
        }
        
        // Update total amount and subtotal
        $order->total = $totalAmount;
        $order->subtotal = $totalAmount; // Assuming no tax/fees for now
        $order->save();

        return $order->load('user', 'items');
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
        
        $this->save();

        return $this->load('user', 'items');
    }
}