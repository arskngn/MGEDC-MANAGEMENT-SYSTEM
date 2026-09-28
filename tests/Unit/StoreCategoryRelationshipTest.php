<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\StoreCategory;
use Tests\TestCase;

class StoreCategoryRelationshipTest extends TestCase
{
    public function test_store_category_relationships_are_defined_on_models(): void
    {
        $this->assertTrue(method_exists(Customer::class, 'storeCategory'));
        $this->assertTrue(method_exists(Sale::class, 'storeCategory'));
        $this->assertTrue(method_exists(StoreCategory::class, 'customers'));
        $this->assertTrue(method_exists(StoreCategory::class, 'sales'));
    }
}
