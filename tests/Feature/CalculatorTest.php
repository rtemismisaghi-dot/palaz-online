<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPricingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_area_calculation_applies_waste(): void
    {
        $category=Category::create(['slug'=>'carpet','name'=>'موکت','is_active'=>true]);
        $product=Product::create(['category_id'=>$category->id,'slug'=>'calc-carpet','name'=>'موکت تست','price'=>100000,'unit'=>'مترمربع','is_active'=>true]);
        ProductPricingRule::create(['product_id'=>$product->id,'calculation_type'=>'area','unit'=>'m2','waste_percent'=>10,'is_active'=>true]);
        $this->postJson('/calculate/calc-carpet',['quantity'=>20])->assertOk()->assertJsonPath('billable_quantity',22)->assertJsonPath('total',2200000);
    }

    public function test_invalid_zero_quantity_is_rejected(): void
    {
        $category=Category::create(['slug'=>'spc','name'=>'SPC','is_active'=>true]);
        $product=Product::create(['category_id'=>$category->id,'slug'=>'calc-spc','name'=>'SPC تست','price'=>100000,'unit'=>'مترمربع','is_active'=>true]);
        ProductPricingRule::create(['product_id'=>$product->id,'calculation_type'=>'area','unit'=>'m2','waste_percent'=>0,'is_active'=>true]);
        $this->postJson('/calculate/calc-spc',['quantity'=>0])->assertStatus(422);
    }
}