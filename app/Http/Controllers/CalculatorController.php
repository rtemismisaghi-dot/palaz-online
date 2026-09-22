<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\ProductCalculator;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function show(string $id)
    {
        $product = Product::with('pricingRule')->where('slug', $id)->where('is_active', true)->firstOrFail();

        return view('store.calculator', ['product' => $product]);
    }

    public function calculate(Request $request, string $id)
    {
        $product = Product::with('pricingRule')->where('slug', $id)->where('is_active', true)->firstOrFail();

        $rules = [
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:100000'],
        ];

        if ($product->pricingRule?->calculation_type === 'roll') {
            $rules['quantity'] = ['required', 'integer', 'min:1', 'max:1000'];
            $rules['length'] = ['required', 'numeric', 'min:1', 'max:15'];
        }

        $data = $request->validate($rules);

        return response()->json(ProductCalculator::quote($product, $data));
    }
}
