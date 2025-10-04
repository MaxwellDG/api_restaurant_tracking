<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrdersController extends Controller
{
    public function index()
    {
        return Order::all();
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