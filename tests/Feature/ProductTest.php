<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\TestCase;

/**
 * PRODUCT TESTS
 * CRUD, low stock, soft delete
 */
class ProductTest extends TestCase
{
    // ──────────────────────────────────────────────
    // TEST 39: Bina auth ke products nahi milte
    // ──────────────────────────────────────────────
    public function test_products_require_authentication(): void
    {
        $tenant = $this->createTenant();

        $this->getJson('/api/v1/owner/products', $this->tenantHeaders($tenant))
             ->assertStatus(401);
    }

    // ──────────────────────────────────────────────
    // TEST 40: Low stock route bhi auth maangta hai
    // ──────────────────────────────────────────────
    public function test_low_stock_route_requires_auth(): void
    {
        $tenant = $this->createTenant();

        $this->getJson('/api/v1/owner/products-low-stock', $this->tenantHeaders($tenant))
             ->assertStatus(401);
    }

    // ──────────────────────────────────────────────
    // TEST 41: Owner naya product create kar sakta hai
    // ──────────────────────────────────────────────
    public function test_owner_can_create_product(): void
    {
        $tenant = $this->createTenant();
        $owner  = $this->createOwner($tenant);
        $this->bindTenant($tenant);

        $response = $this->postJson('/api/v1/owner/products', [
            'name'                => 'Argan Hair Oil',
            'category'            => 'hair',
            'price'               => 450,
            'quantity'            => 30,
            'low_stock_threshold' => 5,
        ], $this->ownerHeaders($owner, $tenant));

        
        $response->assertStatus(201)
                 ->assertJson(['message' => 'Product added successfully']);

        $this->assertDatabaseHas('products', [
            'name'      => 'Argan Hair Oil',
            'tenant_id' => $tenant->id,
        ]);
    }

    // ──────────────────────────────────────────────
    // TEST 42: Low stock products correctly filter hote hain
    // ──────────────────────────────────────────────
    public function test_low_stock_endpoint_returns_correct_products(): void
    {
        $tenant = $this->createTenant();
        $owner  = $this->createOwner($tenant);
        $this->bindTenant($tenant);

        // Low stock product (2 hai, threshold 5 hai)
        Product::create([
            'tenant_id'           => $tenant->id,
            'name'                => 'Finishing Serum',
            'price'               => 299,
            'quantity'            => 2,
            'low_stock_threshold' => 5,
            'is_active'           => true,
        ]);

        // Normal stock product (50 hai, threshold 5 hai)
        Product::create([
            'tenant_id'           => $tenant->id,
            'name'                => 'Shampoo',
            'price'               => 150,
            'quantity'            => 50,
            'low_stock_threshold' => 5,
            'is_active'           => true,
        ]);

        $response = $this->getJson('/api/v1/owner/products-low-stock',
            $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Finishing Serum', $data[0]['name']);
    }
}
