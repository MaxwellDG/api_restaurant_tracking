<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\Items\CreateItemRequest;
use App\Http\Requests\Product\Items\UpdateItemRequest;
use App\Http\Traits\HasCompanyScope;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ItemsController extends Controller
{
    use HasCompanyScope;
    
    public function index()
    {
        return Item::all();
    }

    public function show(Item $item)
    {
        return $item;
    }

    public function store(CreateItemRequest $request)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized. Only admin can create items.'], 403);
        }
        
        // Automatically inject company_id from authenticated user
        return Item::create($request->validatedWithCompany());
    }

    public function update(UpdateItemRequest $request, Item $item)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized. Only admin can update items.'], 403);
        }
        return $item->update($request->all());
    }

    public function destroy(Item $item)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized. Only admin can delete items.'], 403);
        }
        return $item->delete();
    }
}