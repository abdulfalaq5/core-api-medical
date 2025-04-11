<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Requests\ProductRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="API Endpoints for Products"
 * )
 */
class ProductsController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get all products",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="sku", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="price", type="number", format="float"),
     *                 @OA\Property(property="stock", type="integer"),
     *                 @OA\Property(property="category_id", type="string", format="uuid"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index()
    {
        try {
            $products = Product::with('category')->paginate(10);
            $formattedProducts = $products->getCollection()->map(function ($product) {
                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'category' => [
                        'id' => $product->category->id,
                        'name' => $product->category->name
                    ],
                    'createdAt' => toMilliseconds($product->created_at)
                ];
            });
            
            return $this->successResponse([
                'data' => $formattedProducts,
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch products', 500);
        }
    }
    
    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sku", "name", "price", "stock", "category_id"},
     *             @OA\Property(property="sku", type="string", example="PROD-001"),
     *             @OA\Property(property="name", type="string", example="Product Name"),
     *             @OA\Property(property="price", type="number", format="float", example=100.00),
     *             @OA\Property(property="stock", type="integer", example=10),
     *             @OA\Property(property="category_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="sku", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="stock", type="integer"),
     *             @OA\Property(property="category_id", type="string", format="uuid"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(ProductRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $product = Product::create($validated);
            $product->load('category');
            
            $formattedProduct = [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ],
                'createdAt' => toMilliseconds($product->created_at)
            ];
            
            DB::commit();
            return $this->successResponse($formattedProduct, 'Product created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating product: ' . $e->getMessage());
            return $this->errorResponse('Failed to create product', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get product by ID",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="sku", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="stock", type="integer"),
     *             @OA\Property(property="category_id", type="string", format="uuid"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show(Product $product)
    {
        try {
            $product->load('category');
            $formattedProduct = [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ],
                'createdAt' => toMilliseconds($product->created_at)
            ];
            
            return $this->successResponse($formattedProduct);
        } catch (\Exception $e) {
            Log::error('Error fetching product: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch product', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Update a product",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sku", "name", "price", "stock", "category_id"},
     *             @OA\Property(property="sku", type="string", example="PROD-001"),
     *             @OA\Property(property="name", type="string", example="Updated Product Name"),
     *             @OA\Property(property="price", type="number", format="float", example=150.00),
     *             @OA\Property(property="stock", type="integer", example=20),
     *             @OA\Property(property="category_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="sku", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="stock", type="integer"),
     *             @OA\Property(property="category_id", type="string", format="uuid"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(ProductRequest $request, Product $product)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $product->update($validated);
            $product->load('category');
            
            $formattedProduct = [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ],
                'createdAt' => toMilliseconds($product->created_at)
            ];
            
            DB::commit();
            return $this->successResponse($formattedProduct, 'Product updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating product: ' . $e->getMessage());
            return $this->errorResponse('Failed to update product', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Delete a product",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Product deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();
            $product->delete();
            DB::commit();
            return $this->successResponse(null, 'Product deleted successfully', 204);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting product: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete product', 500);
        }
    }
}
