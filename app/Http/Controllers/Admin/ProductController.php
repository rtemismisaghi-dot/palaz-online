<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductPricingRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'pricingRule', 'media']);

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('slug', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        }

        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'desc');
        $allowedSorts = ['id', 'name', 'price', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'id';
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        $products = $query->orderBy($sort, $direction)->paginate(25)->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new Product,
            'categories' => Category::orderBy('sort_order')->get(),
            'rule' => new ProductPricingRule([
                'calculation_type' => 'fixed',
                'unit' => 'item',
                'waste_percent' => 0,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        return $this->save($request, new Product);
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product->load('media'),
            'categories' => Category::orderBy('sort_order')->get(),
            'rule' => $product->pricingRule ?? new ProductPricingRule([
                'calculation_type' => 'fixed',
                'unit' => 'item',
                'waste_percent' => 0,
            ]),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        return $this->save($request, $product);
    }

    public function destroy(Product $product)
    {
        $product->load('media');
        foreach ($product->media as $media) {
            Storage::disk('public')->delete($media->path);
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'محصول حذف شد.');
    }

    public function uploadMedia(Request $request, Product $product)
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'alt' => ['nullable', 'string', 'max:180'],
        ]);

        $path = $data['image']->store('products', 'public');
        $product->media()->create([
            'path' => $path,
            'alt' => $data['alt'] ?? $product->name,
            'sort_order' => ((int) $product->media()->max('sort_order')) + 1,
            'is_cover' => !$product->media()->exists(),
        ]);

        return back()->with('success', 'عکس محصول اضافه شد.');
    }

    public function deleteMedia(Product $product, ProductMedia $media)
    {
        abort_unless($media->product_id === $product->id, 404);

        $wasCover = $media->is_cover;
        Storage::disk('public')->delete($media->path);
        $media->delete();

        if ($wasCover) {
            $product->media()->orderBy('sort_order')->orderBy('id')->first()?->update(['is_cover' => true]);
        }

        return back()->with('success', 'عکس حذف شد.');
    }

    public function setCover(Product $product, ProductMedia $media)
    {
        abort_unless($media->product_id === $product->id, 404);

        DB::transaction(function () use ($product, $media) {
            $product->media()->update(['is_cover' => false]);
            $media->update(['is_cover' => true]);
        });

        return back()->with('success', 'عکس اصلی محصول تغییر کرد.');
    }

    private function save(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:180',
            'slug' => 'required|string|max:180|alpha_dash|unique:products,slug,' . ($product->id ?: 'NULL'),
            'description' => 'nullable|string|max:3000',
            'price' => 'nullable|numeric|min:0',
            'unit' => 'required|string|max:60',
            'tone' => 'nullable|string|max:40',
            'attributes_json' => 'nullable|json',
            'calculation_type' => 'required|in:fixed,area,roll,quantity',
            'calculation_unit' => 'required|string|max:40',
            'waste_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $attributes = !empty($data['attributes_json'])
            ? json_decode($data['attributes_json'], true, 512, JSON_THROW_ON_ERROR)
            : null;

        DB::transaction(function () use ($data, $attributes, $product, $request) {
            $product->fill([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'] ?? null,
                'unit' => $data['unit'],
                'tone' => $data['tone'] ?? null,
                'attributes' => $attributes,
                'is_active' => $request->boolean('is_active'),
                'is_featured' => $request->boolean('is_featured'),
            ]);
            $product->save();

            ProductPricingRule::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'calculation_type' => $data['calculation_type'],
                    'unit' => $data['calculation_unit'],
                    'waste_percent' => $data['waste_percent'] ?? 0,
                    'is_active' => true,
                ]
            );
        });

        return redirect()->route('admin.products.index')->with('success', 'محصول و منطق قیمت‌گذاری ذخیره شد.');
    }
}
