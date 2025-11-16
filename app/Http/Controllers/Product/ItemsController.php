<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\Items\CreateItemRequest;
use App\Http\Requests\Product\Items\UpdateItemRequest;
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
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized. Only admin can update items.'], 403);
        }
        $item->update($request->all());
        return response()->json(new ItemResource($item));
    }

    public function destroy(Item $item)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized. Only admin can delete items.'], 403);
        }
        return $item->delete();
    }
}