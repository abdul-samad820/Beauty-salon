<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\TestCase;

/**
 * INVENTORY TESTS
 * Tests for stock-in, stock-out, low stock thresholds, and access validation.
 */
class InventoryTest extends TestCase
{
    private function createProduct($tenant, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'tenant_id' => $tenant->id,
            'name' => 'Test Product',
            'category' => 'skincare',
            'price' => 299.00,
            'quantity' => 50,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ], $overrides));
    }

    // ──────────────────────────────────────────────
    // TEST 29: Verify owner can successfully add stock
    // ──────────────────────────────────────────────
    public function test_owner_can_add_stock(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $product = $this->createProduct($tenant, ['quantity' => 10]);
        $this->bindTenant($tenant);

        $response = $this->postJson('/api/v1/owner/inventory/stock-in', [
            'product_id' => $product->id,
            'quantity' => 20,
            'reason' => 'Monthly restock',
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Stock added successfully', 'added' => 20]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => 30]);
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 20,
        ]);
    }

    // ──────────────────────────────────────────────
    // TEST 30: Verify owner can successfully deduct stock
    // ──────────────────────────────────────────────
    public function test_owner_can_deduct_stock(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $product = $this->createProduct($tenant, ['quantity' => 50]);
        $this->bindTenant($tenant);

        $response = $this->postJson('/api/v1/owner/inventory/stock-out', [
            'product_id' => $product->id,
            'quantity' => 5,
            'reason' => 'Used in facial service',
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Stock deducted successfully']);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => 45]);
    }

    // ──────────────────────────────────────────────
    // TEST 31: Verify stock deduction fails with insufficient stock
    // ──────────────────────────────────────────────
    public function test_stock_out_fails_when_insufficient_stock(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);
        $product = $this->createProduct($tenant, ['quantity' => 3]);
        $this->bindTenant($tenant);

        $response = $this->postJson('/api/v1/owner/inventory/stock-out', [
            'product_id' => $product->id,
            'quantity' => 10, // Cannot deduct more than available
            'reason' => 'Transaction failure test',
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(400)
            ->assertJson(['message' => 'Insufficient stock!']);
    }

    // ──────────────────────────────────────────────
    // TEST 32: Verify inventory routes require authentication
    // ──────────────────────────────────────────────
    public function test_inventory_routes_require_authentication(): void
    {
        $tenant = $this->createTenant();

        $this->postJson('/api/v1/owner/inventory/stock-in',
            ['product_id' => 1, 'quantity' => 5],
            $this->tenantHeaders($tenant)
        )->assertStatus(401);
    }
}
