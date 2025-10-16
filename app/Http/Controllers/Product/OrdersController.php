<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\IndexOrdersRequest;
use App\Http\Requests\Product\Orders\CreateOrderRequest;
use App\Http\Requests\Product\Orders\UpdateOrderRequest;
use App\Http\Requests\Product\Orders\PayOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    public function index(IndexOrdersRequest $request)
    {
        $query = Order::with(['items']);

        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->startDate);
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->endDate);
        }

        if ($request->has('item_id')) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('items.id', $request->item_id);
            });
        }

        $page = $request->get('page', 1);
        $orders = $query->paginate(25, ['*'], 'page', $page);

        return response()->json($orders);
    }

    public function show(Order $order)
    {
        return $order->load(['user', 'items']);
    }

    public function store(CreateOrderRequest $request)
    {
        $user_id = Auth::id();
        return Order::create($user_id, $request->all());
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized. Only the owner of the order or admin can update.'], 403);
        }
        
        return $order->updateOrder($request->all());
    }

    public function destroy(Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized. Only the owner of the order or admin can delete.'], 403);
        }
        return $order->delete();
    }
}