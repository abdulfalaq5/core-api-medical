<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Traits\ApiResponse;
use App\Http\Requests\CategoryRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helpers\RabbitMQProducer;
/**
 * @OA\Tag(
 *     name="Categories",
 *     description="API Endpoints for Categories"
 * )
 */
class CategoriesController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Get all categories",
     *     tags={"Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="createdAt", type="integer", format="int64", description="Timestamp in milliseconds since epoch")
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
            $categories = Category::select('id', 'name', 'created_at')->get();
            
            $formattedCategories = $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'createdAt' => toMilliseconds($category->created_at)
                ];
            });
            
            return $this->successResponse($formattedCategories, 'Categories retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error fetching categories: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch categories', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Create a new category",
     *     tags={"Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Category Name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="createdAt", type="integer", format="int64", description="Timestamp in milliseconds since epoch")
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
    public function store(CategoryRequest $request)
    {
        try {
            DB::beginTransaction();
            
            // Check for duplicate name
            if (Category::where('name', $request->name)->exists()) {
                DB::rollBack();
                return $this->errorResponse(['name' => ['Nama kategori sudah digunakan']], 422);
            }
            
            $category = Category::create([
                'name' => $request->name
            ]);

            // Publish create message to RabbitMQ
            RabbitMQProducer::publish('data_sync', [
                'action' => 'create',
                'table' => 'categories',
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at
                ]
            ]);

            DB::commit();
            return $this->successResponse([
                'id' => $category->id,
                'name' => $category->name,
                'createdAt' => toMilliseconds($category->created_at)
            ], 'Category created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating category: ' . $e->getMessage());
            return $this->errorResponse('Failed to create category', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     summary="Update a category",
     *     tags={"Categories"},
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
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Updated Category Name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="createdAt", type="integer", format="int64", description="Timestamp in milliseconds since epoch")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
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
    public function update(CategoryRequest $request, $id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return $this->errorResponse('Category not found', 404);
            }

            if (!Str::isUuid($id)) {
                return $this->errorResponse(['id' => ['Invalid UUID format']], 422);
            }

            DB::beginTransaction();
            
            // Check for duplicate name among active categories, excluding current category
            if (Category::where('name', $request->name)
                ->where('id', '!=', $id)
                ->whereNull('deleted_at')
                ->exists()) {
                DB::rollBack();
                return $this->errorResponse(['name' => ['Nama kategori sudah digunakan']], 422);
            }
                
            $category->update([
                'name' => $request->name
            ]);

            // Publish update message to RabbitMQ
            RabbitMQProducer::publish('data_sync', [
                'action' => 'update',
                'table' => 'categories',
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at
                ]
            ]);

            DB::commit();
            return $this->successResponse([
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'createdAt' => toMilliseconds($category->created_at)
                ]
            ], 'Category updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating category: ' . $e->getMessage());
            return $this->errorResponse('Failed to update category', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     summary="Delete a category",
     *     tags={"Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return $this->errorResponse('Category not found', 404);
            }

            if (!Str::isUuid($id)) {
                return $this->errorResponse(['id' => ['Invalid UUID format']], 422);
            }

            DB::beginTransaction();
            $category->delete();
            DB::commit();
            RabbitMQProducer::publish('data_sync', [
                'action' => 'delete',
                'table' => 'categories',
                'data' => [
                    'id' => $category->id
                ]
            ]);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting category: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete category', 500);
        }
    }
}
