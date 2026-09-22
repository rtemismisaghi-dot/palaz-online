<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPricingRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index() { return view('admin.products.index',['products'=>Product::with('category')->latest('id')->get()]); }
    public function create() { return view('admin.products.form',['product'=>new Product,'categories'=>Category::orderBy('sort_order')->get(),'rule'=>new ProductPricingRule(['calculation_type'=>'fixed','unit'=>'item','waste_percent'=>0])]); }
    public function store(Request $request) { return $this->save($request,new Product); }
    public function edit(Product $product) { return view('admin.products.form',['product'=>$product,'categories'=>Category::orderBy('sort_order')->get(),'rule'=>$product->pricingRule ?? new ProductPricingRule(['calculation_type'=>'fixed','unit'=>'item','waste_percent'=>0])]); }
    public function update(Request $request, Product $product) { return $this->save($request,$product); }
    private function save(Request $request, Product $product) {
        $data=$request->validate([
            'category_id'=>'required|exists:categories,id','name'=>'required|string|max:180',
            'slug'=>'required|string|max:180|alpha_dash|unique:products,slug,'.($product->id ?: 'NULL'),
            'description'=>'nullable|string|max:3000','price'=>'nullable|numeric|min:0','unit'=>'required|string|max:60',
            'tone'=>'nullable|string|max:40','calculation_type'=>'required|in:fixed,area,roll,quantity',
            'calculation_unit'=>'required|string|max:40','waste_percent'=>'nullable|numeric|min:0|max:100',
        ]);
        DB::transaction(function() use($data,$product) {
            $product->fill($data); $product->is_active=request()->boolean('is_active'); $product->save();
            ProductPricingRule::updateOrCreate(['product_id'=>$product->id],[
                'calculation_type'=>$data['calculation_type'],'unit'=>$data['calculation_unit'],
                'waste_percent'=>$data['waste_percent'] ?? 0,'is_active'=>true,
            ]);
        });
        return redirect()->route('admin.products.index')->with('success','محصول و منطق قیمت‌گذاری ذخیره شد.');
    }
}