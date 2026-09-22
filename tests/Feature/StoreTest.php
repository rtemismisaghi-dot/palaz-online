<?php

namespace Tests\Feature;

use Database\Seeders\StoreCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StoreCatalogSeeder::class);
    }

    public function test_home_page_loads(): void
    {
        $this->get('/')->assertOk()->assertSee('PALAZ ONLINE')->assertSee('دسته‌بندی‌ها');
    }

    public function test_shop_can_filter_by_category(): void
    {
        $this->get('/shop?category=carpet')
            ->assertOk()
            ->assertSee('موکت پالاز مدل آرتا')
            ->assertDontSee('لمینیت پالاز مدل Classic');
    }

    public function test_product_can_be_added_to_cart(): void
    {
        $this->post('/cart/add/carpet-arta')
            ->assertRedirect('/cart');

        $this->get('/cart')->assertOk()->assertSee('موکت پالاز مدل آرتا');
    }

    public function test_service_request_is_stored_in_session(): void
    {
        $this->post('/services/request', [
            'type' => 'measurement',
            'name' => 'تست کاربر',
            'phone' => '09120000000',
            'description' => 'تست اندازه‌گیری',
        ])->assertRedirect();

        $this->get('/')->assertOk()->assertSee('PALAZ ONLINE');
    }
}
