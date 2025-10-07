<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function create(Request $request){
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. Only admins can create products.'
            ], 403);
        }

        $validated = $request->validate([
        'items' => 'required|string|max:255',
        'price' => 'required|numeric',
        'category' => 'required|string',
        'in_stock' => 'required|numeric',
        ]);

        $product = Products::create($validated);

        return response()->json([
            'message' => 'Product created successfully!',
            'product' => $product
        ], 201);
    }

    public function index()
    {
        $products = Products::all();

        return response()->json([
            'message' => 'All products retrieved successfully.',
            'products' => $products
        ], 200);
    }

    public function show($id)
    {
        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.'
            ], 404);
        }

        return response()->json([
            'message' => 'Product retrieved successfully.',
            'product' => $product
        ], 200);
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. Only admins can delete products.'
            ], 403);
        }
        
        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.'
        ], 200);
    }

    public function update(Request $request, $id)
    {

        $validated = $request->validate([
            'items'    => 'sometimes|string|max:255',
            'price'    => 'sometimes|numeric',
            'category' => 'sometimes|string',
            'in_stock' => 'sometimes|numeric',
        ]);

        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }
        
        if ($request->has('in_stock')) {
            $validated['in_stock'] = $product->in_stock + $request->input('in_stock');
        }
        
        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully!',
            'product' => $product
        ], 200);
    }

    public function search(Request $request, $name)
    {
        $products = Products::where('items', 'LIKE', '%' . $name . '%')->get();

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No products found matching: ' . $name
            ], 404);
        }

        return response()->json([
            'message'  => 'Products found',
            'products' => $products
        ], 200);
    }


}
