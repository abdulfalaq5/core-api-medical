<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\CategoriesController;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoriesControllerTest extends TestCase
{
    use RefreshDatabase;

    private CategoriesController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new CategoriesController();
    }

    public function test_index_returns_all_categories()
    {
        // Create test categories
        $categories = Category::factory()->count(3)->create();

        // Call the index method
        $response = $this->controller->index();

        // Assert response structure
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertCount(3, $responseData['data']);
        
        // Assert each category has correct fields
        foreach ($responseData['data'] as $category) {
            $this->assertArrayHasKey('id', $category);
            $this->assertArrayHasKey('name', $category);
            $this->assertArrayHasKey('createdAt', $category);
            $this->assertTrue(Str::isUuid($category['id']));
            $this->assertIsInt($category['createdAt']);
        }
    }

    public function test_store_creates_new_category()
    {
        // Create mock request
        $request = new CategoryRequest();
        $request->merge([
            'name' => 'Test Category'
        ]);

        // Call the store method
        $response = $this->controller->store($request);

        // Assert response structure
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('Test Category', $responseData['data']['name']);
        $this->assertTrue(Str::isUuid($responseData['data']['id']));
        $this->assertIsInt($responseData['data']['createdAt']);

        // Assert database has the new category
        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category'
        ]);
    }

    public function test_store_prevents_duplicate_category_names()
    {
        // Create existing category
        Category::factory()->create([
            'name' => 'Test Category'
        ]);

        // Create mock request with duplicate name
        $request = new CategoryRequest();
        $request->merge([
            'name' => 'Test Category'
        ]);

        // Call the store method
        $response = $this->controller->store($request);

        // Assert validation error
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $response->getData(true));
    }

    public function test_update_returns_404_for_nonexistent_category()
    {
        // Create mock request
        $request = new CategoryRequest();
        $request->merge([
            'name' => 'Updated Category'
        ]);

        // Call the update method with non-existent ID
        $response = $this->controller->update($request, Str::uuid());

        // Assert 404 response
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_destroy_deletes_category()
    {
        // Create test category
        $category = Category::factory()->create();

        // Call the destroy method
        $response = $this->controller->destroy($category->id);

        // Assert successful response
        $this->assertEquals(204, $response->getStatusCode());

        // Assert category was deleted
        $this->assertSoftDeleted('categories', [
            'id' => $category->id
        ]);
    }

    public function test_destroy_returns_404_for_nonexistent_category()
    {
        // Call the destroy method with non-existent ID
        $response = $this->controller->destroy(Str::uuid());

        // Assert 404 response
        $this->assertEquals(404, $response->getStatusCode());
    }
} 