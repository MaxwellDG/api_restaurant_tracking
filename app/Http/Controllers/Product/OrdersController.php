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
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized. Only the owner of the order or admin can update.'], 403);
        }
        
        $order->updateOrder($request->all());
        return response()->json(new OrderResource($order));
    }

    public function destroy(Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized. Only the owner of the order or admin can delete.'], 403);
        }
        return $order->delete();
    }
}