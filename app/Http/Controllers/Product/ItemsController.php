<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\Items\CreateItemRequest;
use App\Http\Requests\Product\Items\UpdateItemRequest;
use App\Http\Requests\Product\Items\UpdateItemQuantityRequest;
use App\Http\Resources\ItemResource;
use App\Http\Traits\HasCompanyScope;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class ItemsController extends Controller
{
    use HasCompanyScope;
    
    public function index()
    {
        return response()->json(ItemResource::collection(Item::all()));
    }

    public function show(Item $item)
    {
        return response()->json(new ItemResource($item));
    }

    public function store(CreateItemRequest $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized. Only admin can create items.'], 403);
        }
        
        // Automatically inject company_id from authenticated user
        $item = Item::create($request->validatedWithCompany());
        return response()->json(new ItemResource($item), 201);
    }

    public function update(UpdateItemRequest $request, Item $item)
    {
        if (!Auth::user()->isAdmin() || Auth::user()->company_id !== $item->company_id) {
            return response()->json(['error' => 'Unauthorized. Only admin can update items.'], 403);
        }
        $item->update($request->validated());
        return response()->json(new ItemResource($item));
    }

    public function destroy(Item $item)
    {
        if (!Auth::user()->isAdmin() || Auth::user()->company_id !== $item->company_id) {
            return response()->json(['error' => 'Unauthorized. Only admin from the same company can delete items.'], 403);
        }
        return $item->delete();
    }

    public function updateQuantity(UpdateItemQuantityRequest $request, Item $item)
    {
        if (!Auth::user()->isAdmin() || Auth::user()->company_id !== $item->company_id) {
            return response()->json(['error' => 'Unauthorized. Only admin can update item quantity.'], 403);
        }
        
        $change = $request->validated()['quantity'];
        $newQuantity = $item->quantity + $change;
        
        if ($newQuantity < 0) {
            return response()->json(['error' => 'Quantity cannot go below 0.'], 422);
        }
        
        $item->quantity = $newQuantity;
        $item->save();
        
        return response()->json(new ItemResource($item));
    }
}