<?php

namespace Tests\Feature;

use Database\Seeders\StoreCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_and_catalog_pages_load(): void
    {
        $this->seed(StoreCatalogSeeder::class);
        $this->get('/admin')->assertOk()->assertSee('کنسول مدیریت');
        $this->get('/admin/categories')->assertOk()->assertSee('دسته‌بندی‌ها');
        $this->get('/admin/products')->assertOk()->assertSee('محصولات');
    }

    public function test_admin_can_create_category(): void
    {
        $this->post('/admin/categories', [
            'name'=>'کفپوش آزمایشی','slug'=>'test-floor','eyebrow'=>'تست','tone'=>'dark','sort_order'=>20,'is_active'=>1,
        ])->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories',['slug'=>'test-floor','name'=>'کفپوش آزمایشی']);
    }

    public function test_admin_rejects_negative_price(): void
    {
        $this->seed(StoreCatalogSeeder::class);
        $category=\App\Models\Category::first();
        $this->from('/admin/products/create')->post('/admin/products',[
            'category_id'=>$category->id,'name'=>'محصول نامعتبر','slug'=>'invalid-price','price'=>-1,
            'unit'=>'متر','calculation_type'=>'area','calculation_unit'=>'m2','waste_percent'=>0,'is_active'=>1,
        ])->assertSessionHasErrors('price');
    }

    public function test_admin_can_save_product_and_pricing_rule(): void
    {
        $this->seed(StoreCatalogSeeder::class);
        $category=\App\Models\Category::first();
        $this->post('/admin/products',[
            'category_id'=>$category->id,'name'=>'موکت تست','slug'=>'test-carpet','price'=>100000,
            'unit'=>'مترمربع','calculation_type'=>'area','calculation_unit'=>'m2','waste_percent'=>7,'is_active'=>1,
        ])->assertRedirect('/admin/products');
        $this->assertDatabaseHas('products',['slug'=>'test-carpet','price'=>100000]);
        $this->assertDatabaseHas('product_pricing_rules',['calculation_type'=>'area','waste_percent'=>7]);
    }
}