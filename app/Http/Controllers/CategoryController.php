<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
       $validated = $request->validate([
        'per_page' => 'sometimes|integer|min:1|max:100',
        'page' => 'sometimes|integer|min:1',
        'with_products' => 'sometimes|boolean',
       ]);
       $perPage = $validated['per_page'] ?? 10;
       $categories = Category::when($request->boolean('with_products'), function($query) {
        $query->withCount('products');
       })
       ->paginate($perPage);

       return CategoryResource::collection($categories);
    }

    public function show(Category $category)
    {
        return new CategoryResource($category->load('products'));
    }
    public function store(Request $request)
    {
      $validated = $request->validate([
        'name' => 'required|string|max:255|unique:categories',
        'description' => 'nullable|string',
        'slug' => 'nullable|string|unique:categories,slug'
      ]);
      $category = DB::transaction(function() use ($validated) {
        return Category::create($validated);
      });

      return new CategoryResource($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('categories')->ignore($category->id)
            ],
            'description' => 'nullable|string',
            'slug' => [
                'sometimes',
                'string',
                Rule::unique('categories', 'slug')->ignore($category->id)
            ]
            ]);

            $category = DB::transaction(function() use ($category, $validated) {
                $category->update($validated);
                return $category->fresh();
            });

            return new CategoryResource($category);
    }

    public function destroy(Category $category) {
        DB::transaction(function() use ($category) {
            $category->products()->delete();
            $category->delete();
        });
    }

}
