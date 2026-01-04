<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\Category\CreateCategoryRequest;
use App\Http\Requests\Product\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Traits\HasCompanyScope;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoriesController extends Controller
{
    use HasCompanyScope;
    public function index()
    {
        return response()->json(CategoryResource::collection(Category::all()));
    }

    public function show(Category $category)
    {
        return response()->json(new CategoryResource($category));
    }

    public function store(CreateCategoryRequest $request)
    {        
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized. Only admin can create categories.'], 403);
        }
                
        $category = Category::create($request->validatedWithCompany());
        
        return response()->json(new CategoryResource($category), 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized. Only admin can update categories.'], 403);
        }
        $category->update($request->all());
        return response()->json(new CategoryResource($category));
    }

    public function destroy(Category $category)
    {
        if (!Auth::user()->isAdmin()) {
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
            return new CategoryResource($category);
        });
        return response()->json($categories_with_items);
    }
}