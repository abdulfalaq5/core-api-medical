<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\ProductsController;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductsControllerTest extends TestCase
{
    use RefreshDatabase;

    private ProductsController $controller;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ProductsController();
        
        // Create a test category
        $this->category = Category::factory()->create();
    }

    public function test_index_returns_all_products()
    {
        // Create test products
        $products = Product::factory()->count(3)->create([
            'category_id' => $this->category->id
        ]);

        // Call the index method
        $response = $this->controller->index();

        // Assert response structure
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertCount(3, $responseData['data']);
        
        // Assert each product has correct fields
        foreach ($responseData['data'] as $product) {
            $this->assertArrayHasKey('id', $product);
            $this->assertArrayHasKey('sku', $product);
            $this->assertArrayHasKey('name', $product);
            $this->assertArrayHasKey('price', $product);
            $this->assertArrayHasKey('stock', $product);
            $this->assertArrayHasKey('category', $product);
            $this->assertArrayHasKey('createdAt', $product);
            $this->assertTrue(Str::isUuid($product['id']));
            $this->assertTrue(Str::isUuid($product['category']['id']));
        }
    }

    public function test_store_creates_new_product()
    {
        // Create mock request
        $request = new ProductRequest();
        $request->merge([
            'sku' => 'PROD-001',
            'name' => 'Test Product',
            'price' => 100.00,
            'stock' => 10,
            'category_id' => $this->category->id
        ]);

        // Call the store method
        $response = $this->controller->store($request);

        // Assert response structure
        $responseData = $response->getData(true);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('PROD-001', $responseData['data']['sku']);
        $this->assertEquals('Test Product', $responseData['data']['name']);
        $this->assertEquals(100.00, $responseData['data']['price']);
        $this->assertEquals(10, $responseData['data']['stock']);
        $this->assertEquals($this->category->id, $responseData['data']['category']['id']);
        $this->assertTrue(Str::isUuid($responseData['data']['id']));

        // Assert database has the new product
        $this->assertDatabaseHas('products', [
            'sku' => 'PROD-001',
            'name' => 'Test Product',
            'price' => 100.00,
            'stock' => 10,
            'category_id' => $this->category->id
        ]);
    }

    public function test_store_prevents_duplicate_sku()
    {
        // Create existing product
        Product::factory()->create([
            'sku' => 'PROD-001',
            'category_id' => $this->category->id
        ]);

        // Create mock request with duplicate SKU
        $request = new ProductRequest();
        $request->merge([
            'sku' => 'PROD-001',
            'name' => 'Test Product',
            'price' => 100.00,
            'stock' => 10,
            'category_id' => $this->category->id
        ]);

        // Call the store method
        $response = $this->controller->store($request);

        // Assert validation error
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $response->getData(true));
    }

    public function test_show_returns_product()
    {
        // Create test product
        $product = Product::factory()->create([
            'category_id' => $this->category->id
        ]);

        // Call the show method
        $response = $this->controller->show($product);

        // Assert response structure
        $responseData = $response->getData(true);
        $this->assertEquals($product->id, $responseData['data']['id']);
        $this->assertEquals($product->sku, $responseData['data']['sku']);
        $this->assertEquals($product->name, $responseData['data']['name']);
        $this->assertEquals($product->price, $responseData['data']['price']);
        $this->assertEquals($product->stock, $responseData['data']['stock']);
        $this->assertEquals($product->category->id, $responseData['data']['category']['id']);
    }

    public function test_update_modifies_existing_product()
    {
        // Create test product
        $product = Product::factory()->create([
            'category_id' => $this->category->id
        ]);

        // Create mock request
        $request = new ProductRequest();
        $request->merge([
            'sku' => 'PROD-002',
            'name' => 'Updated Product',
            'price' => 150.00,
            'stock' => 20,
            'category_id' => $this->category->id
        ]);

        // Call the update method
        $response = $this->controller->update($request, $product);

        // Assert response structure
        $responseData = $response->getData(true);
        $this->assertEquals('PROD-002', $responseData['data']['sku']);
        $this->assertEquals('Updated Product', $responseData['data']['name']);
        $this->assertEquals(150.00, $responseData['data']['price']);
        $this->assertEquals(20, $responseData['data']['stock']);

        // Assert database was updated
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'sku' => 'PROD-002',
            'name' => 'Updated Product',
            'price' => 150.00,
            'stock' => 20
        ]);
    }

    public function test_destroy_deletes_product()
    {
        // Create test product
        $product = Product::factory()->create([
            'category_id' => $this->category->id
        ]);

        // Call the destroy method
        $response = $this->controller->destroy($product);

        // Assert successful response
        $this->assertEquals(204, $response->getStatusCode());

        // Assert product was deleted
        $this->assertSoftDeleted('products', [
            'id' => $product->id
        ]);
    }
} 