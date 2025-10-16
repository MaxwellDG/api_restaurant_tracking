<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\Category\CreateCategoryRequest;
use App\Http\Requests\Product\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

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

    public function store(CreateCategoryRequest $request)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized. Only admin can create categories.'], 403);
        }
        return Category::create($request->all());
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized. Only admin can update categories.'], 403);
        }
        return $category->update($request->all());
    }
    public function destroy(Category $category)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized. Only admin can delete categories.'], 403);
        }
        return $category->delete();
    }

    /**
     * Get the full inventory of categories and their items
     */
    public function inventory()
    {
        $categories = Category::getCategories();

        $categories_with_items = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'items' => $category->items,
            ];
        });
        return response()->json($categories_with_items);
    }
}