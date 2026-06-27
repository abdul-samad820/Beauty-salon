<?php

namespace Tests\Feature;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Tests\TestCase;

/**
 * INVENTORY VALUATION TESTS
 *
 * Covers the new Owner > Inventory > Valuation report: cost/retail value
 * totals, missing-cost handling, and opening/closing stock derivation
 * from inventory_transactions within the selected period.
 */
class InventoryValuationTest extends TestCase
{
    public function test_owner_can_view_valuation_report(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $response = $this->actingAs($owner)->get('/owner/inventory/valuation');

        $response->assertStatus(200);
        $response->assertViewIs('owner.inventory.valuation');
    }

    public function test_valuation_report_calculates_retail_and_cost_value(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        Product::create([
            'tenant_id' => $tenant->id,
            'name' => 'Shampoo',
            'category' => 'hair',
            'price' => 200,
            'cost_price' => 120,
            'quantity' => 10,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->get('/owner/inventory/valuation');

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return (float) $stats['total_retail_value'] === 2000.0
                && (float) $stats['total_cost_value'] === 1200.0
                && (float) $stats['potential_profit'] === 800.0;
        });
    }

    public function test_valuation_report_flags_products_missing_cost_price(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        Product::create([
            'tenant_id' => $tenant->id,
            'name' => 'Conditioner',
            'category' => 'hair',
            'price' => 250,
            'cost_price' => null,
            'quantity' => 5,
            'low_stock_threshold' => 3,
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->get('/owner/inventory/valuation');

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['missing_cost_count'] === 1
                && (float) $stats['total_cost_value'] === 0.0;
        });
    }

    public function test_valuation_report_excludes_inactive_products(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        Product::create([
            'tenant_id' => $tenant->id,
            'name' => 'Discontinued Item',
            'category' => 'hair',
            'price' => 500,
            'cost_price' => 300,
            'quantity' => 20,
            'low_stock_threshold' => 5,
            'is_active' => false,
        ]);

        $response = $this->actingAs($owner)->get('/owner/inventory/valuation');

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_products'] === 0;
        });
    }

    public function test_valuation_report_derives_opening_stock_from_movements(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'name' => 'Hair Oil',
            'category' => 'hair',
            'price' => 300,
            'cost_price' => 150,
            'quantity' => 15, // current/closing stock
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);

        // Within the period: 10 units came in, 5 went out.
        // Opening = closing(15) - in(10) + out(5) = 10
        InventoryTransaction::create([
            'tenant_id' => $tenant->id,
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 10,
            'reason' => 'Restock',
        ]);
        InventoryTransaction::create([
            'tenant_id' => $tenant->id,
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 5,
            'reason' => 'Service use',
        ]);

        $response = $this->actingAs($owner)->get('/owner/inventory/valuation?days=30');

        $response->assertStatus(200);
        $response->assertViewHas('valuationRows', function ($rows) {
            $row = $rows->firstWhere('name', 'Hair Oil');

            return $row
                && $row['opening_stock'] === 10
                && $row['stock_in'] === 10
                && $row['stock_out'] === 5
                && $row['closing_stock'] === 15;
        });
    }

    public function test_owner_can_create_product_with_cost_price(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $response = $this->actingAs($owner)->post('/owner/inventory', [
            'name' => 'Face Cream',
            'category' => 'skin',
            'price' => 400,
            'cost_price' => 250,
            'quantity' => 8,
            'low_stock_threshold' => 3,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('products', [
            'tenant_id' => $tenant->id,
            'name' => 'Face Cream',
            'price' => 400,
            'cost_price' => 250,
        ]);
    }

    public function test_owner_cannot_view_another_tenants_valuation_data(): void
    {
        $tenantA = $this->createTenant(['slug' => 'salon-a', 'subdomain' => 'salon-a', 'email' => 'a@test.com']);
        $tenantB = $this->createTenant(['slug' => 'salon-b', 'subdomain' => 'salon-b', 'email' => 'b@test.com']);

        $ownerA = $this->createOwner($tenantA, ['email' => 'ownera@test.com']);

        Product::create([
            'tenant_id' => $tenantB->id,
            'name' => 'Tenant B Exclusive Serum',
            'category' => 'skin',
            'price' => 1000,
            'cost_price' => 600,
            'quantity' => 5,
            'low_stock_threshold' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($ownerA)->get('/owner/inventory/valuation');

        $response->assertStatus(200);
        $response->assertViewHas('valuationRows', function ($rows) {
            return $rows->firstWhere('name', 'Tenant B Exclusive Serum') === null;
        });
    }
}
