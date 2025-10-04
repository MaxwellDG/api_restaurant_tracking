<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\IndexOrdersRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrdersController extends Controller
{
    public function index(IndexOrdersRequest $request)
    {
        $query = Order::with(['user', 'items']);

        if ($request->has('startDate')) {
            $query->where('created_at', '>=', $request->startDate);
        }

        if ($request->has('endDate')) {
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
        return $order;
    }

    public function store(Request $request)
    {
        return Order::create($request->all());
    }

    public function update(Request $request, Order $order)
    {
        return $order->update($request->all());
    }

    public function destroy(Order $order)
    {
        return $order->delete();
    }
}