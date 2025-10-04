<?php

namespace App\Http\Controllers\Items;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ItemsController extends Controller
{
    public function index()
    {
        return Item::all();
    }

    public function show(Item $item)
    {
        return $item;
    }

    public function store(Request $request)
    {
        return Item::create($request->all());
    }

    public function update(Request $request, Item $item)
    {
        return $item->update($request->all());
    }

    public function destroy(Item $item)
    {
        return $item->delete();
    }
}