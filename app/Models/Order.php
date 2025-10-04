<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'total_amount', 'completed_at', 'paid_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'orders_items')
                    ->withPivot('quantity', 'unit_price')
                    ->withTimestamps();
    }

    /**
     * Create a new order with items
     */
    public static function create(int $user_id, array $data)
    {
        $order = new static();
        $order->user_id = $user_id;
        $order->total_amount = $data['total_amount'] ?? 0;
        $order->save();

        // Attach items if provided
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $order->items()->attach($item['item_id'], [
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price']
                ]);
            }
        }

        return $order->load('user', 'items');
    }

    /**
     * Update an order with items
     */
    public function updateOrder(array $data)
    {
        if (isset($data['total_amount'])) {
            $this->total_amount = $data['total_amount'];
        }
        
        if (isset($data['items'])) {
            $this->items()->sync($data['items']);
        }
        
        if (isset($data['completed_at'])) {
            $this->completed_at = $data['completed_at'];
        }
        
        if (isset($data['paid_at'])) {
            $this->paid_at = $data['paid_at'];
        }
        
        $this->save();

        return $this->load('user', 'items');
    }
}