<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ServiceRequest;
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

    public function test_checkout_persists_order_and_items(): void
    {
        $this->post('/cart/add/carpet-arta', ['quantity' => 2])->assertRedirect('/cart');

        $this->post('/checkout', [
            'name' => 'تست سفارش',
            'phone' => '09120000000',
            'city' => 'تهران',
            'postal_code' => '1234567890',
            'address' => 'آدرس تست',
            'service' => 'measurement',
            'payment' => 'offline',
        ])->assertOk()->assertSee('سفارش شما ثبت شد.');

        $this->assertDatabaseHas('orders', [
            'name' => 'تست سفارش',
            'phone' => '09120000000',
            'service' => 'measurement',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_name' => 'موکت پالاز مدل آرتا',
            'quantity' => 2,
        ]);

        $this->assertCount(1, Order::with('items')->get());
    }

    public function test_service_request_is_persisted_with_target_system(): void
    {
        $this->post('/services/request', [
            'type' => 'measurement',
            'name' => 'تست کاربر',
            'phone' => '09120000000',
            'description' => 'تست اندازه‌گیری',
        ])->assertRedirect()->assertSessionHas('service_success');

        $this->assertDatabaseHas('service_requests', [
            'type' => 'measurement',
            'name' => 'تست کاربر',
            'target_system' => 'dtz',
            'status' => 'received',
        ]);

        $this->assertCount(1, ServiceRequest::all());
    }

}
