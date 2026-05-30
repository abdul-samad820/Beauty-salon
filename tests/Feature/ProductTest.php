<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_products_require_authentication()
    {
        $response = $this->getJson(
            '/api/v1/owner/products',
            [
                'X-Tenant' => 'demo',
            ]
        );

        $response->assertStatus(401);
    }

    public function test_low_stock_route_requires_auth()
    {
        $response = $this->getJson(
            '/api/v1/owner/products-low-stock',
            [
                'X-Tenant' => 'demo',
            ]
        );

        $response->assertStatus(401);
    }
}
