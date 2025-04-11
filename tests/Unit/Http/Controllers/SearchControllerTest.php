<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\SearchController;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    private SearchController $controller;
    private Category $category1;
    private Category $category2;
    private Product $product1;
    private Product $product2;
    private Product $product3;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new SearchController();

        // Create test categories
        $this->category1 = Category::factory()->create(['name' => 'Electronics']);
        $this->category2 = Category::factory()->create(['name' => 'Clothing']);

        // Create test products
        $this->product1 = Product::factory()->create([
            'sku' => 'PROD-001',
            'name' => 'Laptop',
            'price' => 1000.00,
            'stock' => 10,
            'category_id' => $this->category1->id
        ]);

        $this->product2 = Product::factory()->create([
            'sku' => 'PROD-002',
            'name' => 'Smartphone',
            'price' => 500.00,
            'stock' => 20,
            'category_id' => $this->category1->id
        ]);

        $this->product3 = Product::factory()->create([
            'sku' => 'PROD-003',
            'name' => 'T-Shirt',
            'price' => 20.00,
            'stock' => 50,
            'category_id' => $this->category2->id
        ]);
    }

    public function test_search_by_sku()
    {
        $request = new Request([
            'sku' => 'PROD-001,PROD-002'
        ]);

        $response = $this->controller->search($request);
        $data = $response->getData(true);

        $this->assertEquals(2, count($data['data']));
        $this->assertEquals('PROD-001', $data['data'][0]['sku']);
        $this->assertEquals('PROD-002', $data['data'][1]['sku']);
    }

    public function test_search_by_name()
    {
        $request = new Request([
            'name' => 'Laptop,Smartphone'
        ]);

        $response = $this->controller->search($request);
        $data = $response->getData(true);

        $this->assertEquals(2, count($data['data']));
        $this->assertEquals('Laptop', $data['data'][0]['name']);
        $this->assertEquals('Smartphone', $data['data'][1]['name']);
    }

    public function test_search_by_stock_range()
    {
        $request = new Request([
            'stock_start' => 15,
            'stock_end' => 60
        ]);

        $response = $this->controller->search($request);
        $data = $response->getData(true);

        $this->assertEquals(2, count($data['data']));
        $this->assertEquals('Smartphone', $data['data'][0]['name']);
        $this->assertEquals('T-Shirt', $data['data'][1]['name']);
    }

    public function test_search_by_category_id()
    {
        $request = new Request([
            'category_id' => $this->category1->id
        ]);

        $response = $this->controller->search($request);
        $data = $response->getData(true);

        $this->assertEquals(2, count($data['data']));
        $this->assertEquals('Laptop', $data['data'][0]['name']);
        $this->assertEquals('Smartphone', $data['data'][1]['name']);
    }

    public function test_search_by_category_name()
    {
        $request = new Request([
            'category_name' => 'Electronics'
        ]);

        $response = $this->controller->search($request);
        $data = $response->getData(true);

        $this->assertEquals(2, count($data['data']));
        $this->assertEquals('Laptop', $data['data'][0]['name']);
        $this->assertEquals('Smartphone', $data['data'][1]['name']);
    }

    public function test_search_with_pagination()
    {
        $request = new Request([
            'page' => 1,
            'size' => 2
        ]);

        $response = $this->controller->search($request);
        $data = $response->getData(true);

        $this->assertEquals(2, count($data['data']));
        $this->assertEquals(2, $data['paging']['size']);
        $this->assertEquals(1, $data['paging']['current']);
    }

    public function test_search_with_multiple_filters()
    {
        $request = new Request([
            'category_id' => $this->category1->id,
            'price_start' => 400,
            'price_end' => 1500,
            'stock_start' => 5,
            'stock_end' => 15
        ]);

        $response = $this->controller->search($request);
        $data = $response->getData(true);

        $this->assertEquals(1, count($data['data']));
        $this->assertEquals('Laptop', $data['data'][0]['name']);
    }

    public function test_search_with_invalid_price_range()
    {
        $request = new Request([
            'price_start' => 600,
            'price_end' => 400
        ]);

        $response = $this->controller->search($request);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_search_with_invalid_stock_range()
    {
        $request = new Request([
            'stock_start' => 30,
            'stock_end' => 20
        ]);

        $response = $this->controller->search($request);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_search_with_invalid_category_id()
    {
        $request = new Request([
            'category_id' => 'invalid-uuid'
        ]);

        $response = $this->controller->search($request);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_search_with_invalid_page_size()
    {
        $request = new Request([
            'size' => 200
        ]);

        $response = $this->controller->search($request);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_search_with_empty_parameters()
    {
        $request = new Request();

        $response = $this->controller->search($request);
        $data = $response->getData(true);

        $this->assertEquals(3, count($data['data']));
        $this->assertEquals(10, $data['paging']['size']);
        $this->assertEquals(1, $data['paging']['current']);
    }
} 