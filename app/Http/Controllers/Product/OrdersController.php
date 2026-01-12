<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\Orders\CreateOrderRequest;
use App\Http\Requests\Product\Orders\IndexOrdersRequest;
use App\Http\Requests\Product\Orders\UpdateOrderRequest;
use App\Http\Resources\OrderIndexResource;
use App\Http\Resources\OrderResource;
use App\Http\Traits\HasCompanyScope;
use App\Models\Order;
use App\Repositories\OrdersRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    use HasCompanyScope;

    public function __construct(
        private OrdersRepository $ordersRepository
    ) {}

    public function index(IndexOrdersRequest $request)
    {        
        $orders = $this->ordersRepository->getOrders(
            $this->getAuthCompanyId(),
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('status'),
            $request->input('page', 1)
        );

        return response()->json([
            'data' => OrderIndexResource::collection($orders),
            'pagination' => [
                'currentPage' => $orders->currentPage(),
                'totalPages' => $orders->lastPage(),
                'totalCount' => $orders->total(),
                'hasNext' => $orders->hasMorePages(),
                'hasPrevious' => $orders->currentPage() > 1,
            ],
        ]);
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
        if (Auth::user()->company_id !== $order->company_id) {
            return response()->json(['error' => 'Unauthorized. Only orders from the same company can be updated.'], 403);
        }
        
        $order->updateOrder($request->all());
        return response()->json(new OrderResource($order));
    }

    public function destroy(Order $order)
    {
        $user = Auth::user();
        
        // Check if user is from the same company
        if ($user->company_id !== $order->company_id) {
            return response()->json(['error' => 'Unauthorized. You can only delete orders from your company.'], 403);
        }
        
        // Allow if user is admin OR if user created the order
        if ($user->isAdmin() || $order->user_id === $user->id) {
            return $order->delete();
        }
        
        return response()->json(['error' => 'Unauthorized. You can only delete your own orders.'], 403);
    }

    public function addItems(Request $request, Order $order)
    {
        $user = Auth::user();
        
        // Check if user is from the same company
        if ($user->company_id !== $order->company_id) {
            return response()->json(['error' => 'Unauthorized. You can only modify orders from your company.'], 403);
        }
        
        // Only allow if user is admin OR if user created the order
        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized. You can only modify your own orders.'], 403);
        }
        
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
        
        try {
            $updatedOrder = $order->addItems($request->input('items'));
            return response()->json(new OrderResource($updatedOrder));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function removeItems(Request $request, Order $order)
    {
        $user = Auth::user();
        
        // Check if user is from the same company
        if ($user->company_id !== $order->company_id) {
            return response()->json(['error' => 'Unauthorized. You can only modify orders from your company.'], 403);
        }
        
        // Only allow if user is admin OR if user created the order
        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized. You can only modify your own orders.'], 403);
        }
        
        $request->validate([
            'order_item_ids' => 'required|array|min:1',
            'order_item_ids.*' => 'required|integer',
        ]);
        
        try {
            $updatedOrder = $order->removeItems($request->input('order_item_ids'));
            return response()->json(new OrderResource($updatedOrder));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}