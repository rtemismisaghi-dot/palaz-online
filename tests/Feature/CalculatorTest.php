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
        $category = Category::create(['slug' => 'carpet', 'name' => 'موکت', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'slug' => 'calc-carpet',
            'name' => 'موکت تست',
            'price' => 100000,
            'unit' => 'مترمربع',
            'is_active' => true,
        ]);
        ProductPricingRule::create([
            'product_id' => $product->id,
            'calculation_type' => 'area',
            'unit' => 'm2',
            'waste_percent' => 10,
            'is_active' => true,
        ]);

        $this->postJson('/calculate/calc-carpet', ['quantity' => 20])
            ->assertOk()
            ->assertJsonPath('billable_quantity', 22)
            ->assertJsonPath('total', 2200000);
    }

    public function test_three_meter_roll_one_meter_is_three_square_meters(): void
    {
        $product = $this->threeMeterCarpet();

        $this->postJson('/calculate/calc-roll', ['quantity' => 1, 'length' => 1])
            ->assertOk()
            ->assertJsonPath('width', 3)
            ->assertJsonPath('length', 1)
            ->assertJsonPath('area_per_roll', 3)
            ->assertJsonPath('billable_quantity', 3)
            ->assertJsonPath('total', 300000);
    }

    public function test_three_meter_roll_fifteen_meter_is_forty_five_square_meters(): void
    {
        $product = $this->threeMeterCarpet();

        $this->postJson('/calculate/calc-roll', ['quantity' => 1, 'length' => 15])
            ->assertOk()
            ->assertJsonPath('billable_quantity', 45)
            ->assertJsonPath('total', 4500000);
    }

    public function test_three_meter_roll_rejects_length_over_fifteen(): void
    {
        $this->threeMeterCarpet();

        $this->postJson('/calculate/calc-roll', ['quantity' => 1, 'length' => 16])
            ->assertStatus(422);
    }

    public function test_invalid_zero_quantity_is_rejected(): void
    {
        $category = Category::create(['slug' => 'spc', 'name' => 'SPC', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'slug' => 'calc-spc',
            'name' => 'SPC تست',
            'price' => 100000,
            'unit' => 'مترمربع',
            'is_active' => true,
        ]);
        ProductPricingRule::create([
            'product_id' => $product->id,
            'calculation_type' => 'area',
            'unit' => 'm2',
            'waste_percent' => 0,
            'is_active' => true,
        ]);

        $this->postJson('/calculate/calc-spc', ['quantity' => 0])
            ->assertStatus(422);
    }

    private function threeMeterCarpet(): Product
    {
        $category = Category::create(['slug' => 'carpet-roll', 'name' => 'موکت طاقه', 'is_active' => true]);

        $product = Product::create([
            'category_id' => $category->id,
            'slug' => 'calc-roll',
            'name' => 'موکت طاقه ۳ متر تست',
            'price' => 100000,
            'unit' => 'مترمربع',
            'is_active' => true,
        ]);

        ProductPricingRule::create([
            'product_id' => $product->id,
            'calculation_type' => 'roll',
            'unit' => 'مترمربع',
            'waste_percent' => 0,
            'parameters' => ['width' => 3, 'min_length' => 1, 'max_length' => 15],
            'is_active' => true,
        ]);

        return $product;
    }
}
