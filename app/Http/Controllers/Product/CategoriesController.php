<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoriesController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function show(Category $category)
    {
        return $category;
    }

    public function store(Request $request)
    {
        return Category::create($request->all());
    }

    public function update(Request $request, Category $category)
    {
        return $category->update($request->all());
    }
    public function destroy(Category $category)
    {
        return $category->delete();
    }
}