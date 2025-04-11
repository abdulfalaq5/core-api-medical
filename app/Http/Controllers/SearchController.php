<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Traits\ApiSearchResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Search",
 *     description="API Endpoints for searching products"
 * )
 */
class SearchController extends Controller
{
    use ApiSearchResponse;

    /**
     * @OA\Get(
     *     path="/api/search",
     *     summary="Search products with various filters",
     *     tags={"Search"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="sku",
     *         in="query",
     *         description="Filter by SKU (comma-separated values)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter by name using LIKE search (comma-separated values)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price_start",
     *         in="query",
     *         description="Filter by minimum price",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="price_end",
     *         in="query",
     *         description="Filter by maximum price",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="stock_start",
     *         in="query",
     *         description="Filter by minimum stock",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="stock_end",
     *         in="query",
     *         description="Filter by maximum stock",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category ID (comma-separated values)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="category_name",
     *         in="query",
     *         description="Filter by category name using LIKE search (comma-separated values)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="size",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="sku", type="string"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="price", type="number", format="float"),
     *                     @OA\Property(property="stock", type="integer"),
     *                     @OA\Property(
     *                         property="category",
     *                         type="object",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="name", type="string")
     *                     ),
     *                     @OA\Property(property="createdAt", type="integer", description="Epoch timestamp in milliseconds")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="paging",
     *                 type="object",
     *                 @OA\Property(property="size", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="current", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to search products")
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        try {
            // Get query parameters
            $queryParams = $request->query();
            
            // Process array parameters from comma-separated strings
            $arrayParams = [
                'sku' => [],
                'name' => [],
                'category_id' => [],
                'category_name' => []
            ];

            foreach ($arrayParams as $param => $default) {
                if (isset($queryParams[$param]) && !empty($queryParams[$param])) {
                    $values = explode(',', $queryParams[$param]);
                    $arrayParams[$param] = array_filter($values, function($value) {
                        return !empty(trim($value));
                    });
                }
            }

            // Merge array parameters with request data
            $request->merge($arrayParams);

            // Handle dot notation parameters
            if ($request->has('price.start')) {
                $request->merge(['price_start' => floatval($request->input('price.start'))]);
            }
            if ($request->has('price.end')) {
                $request->merge(['price_end' => floatval($request->input('price.end'))]);
            }
            if ($request->has('stock.start')) {
                $request->merge(['stock_start' => intval($request->input('stock.start'))]);
            }
            if ($request->has('stock.end')) {
                $request->merge(['stock_end' => intval($request->input('stock.end'))]);
            }
            if ($request->has('category.id')) {
                $request->merge(['category_id' => [$request->input('category.id')]]);
            }

            // Remove dot notation parameters to avoid confusion
            $request->offsetUnset('price.start');
            $request->offsetUnset('price.end');
            $request->offsetUnset('stock.start');
            $request->offsetUnset('stock.end');
            $request->offsetUnset('category.id');

            // Validate input
            $validator = Validator::make($request->all(), [
                'sku' => 'sometimes|array',
                'sku.*' => 'string',
                'name' => 'sometimes|array',
                'name.*' => 'string',
                'price_start' => 'sometimes|numeric|min:0',
                'price_end' => 'sometimes|numeric|min:0|gte:price_start',
                'stock_start' => 'sometimes|integer|min:0',
                'stock_end' => 'sometimes|integer|min:0|gte:stock_start',
                'category_id' => 'sometimes|array',
                'category_id.*' => 'uuid|exists:categories,id',
                'category_name' => 'sometimes|array',
                'category_name.*' => 'string',
                'page' => 'sometimes|integer|min:1',
                'size' => 'sometimes|integer|min:1|max:100'
            ], [
                'price_end.gte' => 'The maximum price must be greater than or equal to the minimum price',
                'stock_end.gte' => 'The maximum stock must be greater than or equal to the minimum stock',
                'category_id.*.exists' => 'The selected category does not exist',
                'size.max' => 'The page size may not be greater than 100'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 422);
            }

            // Build query
            $query = Product::with('category');

            // Apply filters
            $this->applySkuFilter($query, $request);
            $this->applyNameFilter($query, $request);
            $this->applyPriceFilter($query, $request);
            $this->applyStockFilter($query, $request);
            $this->applyCategoryIdFilter($query, $request);
            $this->applyCategoryNameFilter($query, $request);

            // Sort by name to ensure consistent order
            $query->orderBy('name');

            // Paginate results
            $perPage = min($request->input('size', 10), 100); // Limit max page size to 100
            $currentPage = $request->input('page', 1);
            $products = $query->paginate($perPage, ['*'], 'page', $currentPage);

            // Format response
            $formattedProducts = $this->formatProducts($products);

            // Return response in the expected format
            return response()->json([
                'data' => $formattedProducts,
                'paging' => [
                    'size' => $products->perPage(),
                    'total' => $products->total(),
                    'current' => $products->currentPage()
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in product search: ' . $e->getMessage());
            return $this->errorResponse('Failed to search products', 500);
        }
    }

    /**
     * Apply SKU filter to query
     */
    private function applySkuFilter($query, $request)
    {
        if ($request->has('sku') && !empty($request->sku)) {
            $query->whereIn('sku', $request->sku);
        }
    }

    /**
     * Apply name filter to query
     */
    private function applyNameFilter($query, $request)
    {
        if ($request->has('name') && !empty($request->name)) {
            $query->where(function ($q) use ($request) {
                foreach ($request->name as $name) {
                    $q->orWhere('name', 'LIKE', "%{$name}%");
                }
            });
        }
    }

    /**
     * Apply price filter to query
     */
    private function applyPriceFilter($query, $request)
    {
        // Handle both dot notation and underscore parameters
        $priceStart = $request->input('price.start', $request->input('price_start'));
        $priceEnd = $request->input('price.end', $request->input('price_end'));

        if (!empty($priceStart)) {
            $priceStart = floatval($priceStart);
            $query->where('price', '>=', $priceStart);
        }
        if (!empty($priceEnd)) {
            $priceEnd = floatval($priceEnd);
            $query->where('price', '<=', $priceEnd);
        }

        Log::info('Price filter:', ['priceStart' => $priceStart, 'priceEnd' => $priceEnd]);
    }

    /**
     * Apply stock filter to query
     */
    private function applyStockFilter($query, $request)
    {
        if ($request->has('stock_start')) {
            $query->where('stock', '>=', $request->input('stock_start'));
        }
        if ($request->has('stock_end')) {
            $query->where('stock', '<=', $request->input('stock_end'));
        }
    }

    /**
     * Apply category ID filter to query
     */
    private function applyCategoryIdFilter($query, $request)
    {
        if ($request->has('category_id') && !empty($request->input('category_id'))) {
            $query->whereIn('category_id', $request->input('category_id'));
        }
    }

    /**
     * Apply category name filter to query
     */
    private function applyCategoryNameFilter($query, $request)
    {
        if ($request->has('category_name') && !empty($request->input('category_name'))) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where(function ($subQuery) use ($request) {
                    foreach ($request->input('category_name') as $name) {
                        $subQuery->orWhere('name', 'LIKE', "%{$name}%");
                    }
                });
            });
        }
    }

    /**
     * Format products for response
     */
    private function formatProducts($products)
    {
        return $products->getCollection()->map(function ($product) {
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
                'createdAt' => $product->created_at->timestamp * 1000
            ];
        })->toArray();
    }
}
