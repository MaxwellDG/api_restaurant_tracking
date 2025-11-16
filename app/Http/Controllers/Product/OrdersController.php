<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\IndexOrdersRequest;
use App\Http\Requests\Product\Orders\CreateOrderRequest;
use App\Http\Requests\Product\Orders\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Traits\HasCompanyScope;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    use HasCompanyScope;
    public function index(IndexOrdersRequest $request)
    {
        $query = Order::with(['items']);

        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->startDate);
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->endDate);
        }

        $page = $request->get('page', 1);
        $orders = $query->paginate(25, ['*'], 'page', $page);

        return response()->json(OrderResource::collection($orders));
    }

    public function show(Order $order)
    {
        return response()->json(new OrderResource($order->load(['user', 'items'])));
    }

    public function store(CreateOrderRequest $request)
    {
        $user_id = Auth::id();
        
        // Automatically inject company_id from authenticated user
        $data = $request->validatedWithCompany();
        
        $order = Order::createWithItems($user_id, $data);
        return response()->json(new OrderResource($order), 201);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized. Only the owner of the order or admin can update.'], 403);
        }
        
        $order->updateOrder($request->all());
        return response()->json(new OrderResource($order));
    }

    public function destroy(Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized. Only the owner of the order or admin can delete.'], 403);
        }
        return $order->delete();
    }
}