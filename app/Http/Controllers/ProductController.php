<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
            'search' => 'sometimes|string|max:255',
            'category' => 'sometimes|exists:categories,id',
            'sort_by' => 'sometimes|in:name,price,created_at',
            'sort_order' => 'sometimes|in:asc,desc',

        ]);

        $perPage = $validated['per_page'] ?? 15;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';

        $products = Product::with('category')
            ->when($request->search, function($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })
            ->when($request->category, function($query) use ($request) {
                $query->where('category_id', $request->category);
            })
            ->orderBy($sortBy, $sortOrder)
          ->paginate($perPage);

        return ProductResource::collection($products);      
    }

    public function show(Product $product)
    {
        return new ProductResource($product->load('category'));
    }
    
    public function store(Request $request)
    {
      $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'category_id' => 'required|exists:categories,id',
        'image' => 'required|image|max:2048',
      ]);
      // Handle image upload
      $validated['image_path'] = $request->file('image')->store('products', 'public');
    
      $product = Product::create($validated);

      return response()->json([
        'message' => 'Product created successfully',
        'data' => new ProductResource($product),
      ], 201);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'image' => 'sometimes|image|max:2048',
            '_method' => 'sometimes'
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($product->image_path);
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($validated['_method']);
        $product->update($validated);
        return response()->json([
            'message' => 'Product updated successfully',
            'data' => new ProductResource($product),
        ]);
    }

    public function destroy(Product $product)
    {
        Storage::disk('public')->delete($product->image_path);
        $product->delete();
        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
    
}
