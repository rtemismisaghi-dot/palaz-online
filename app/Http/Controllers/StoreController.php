<?php

namespace App\Http\Controllers;

use App\Support\StoreCatalog;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function home()
    {
        return view('store.home', [
            'categories' => StoreCatalog::categories(),
            'products' => array_slice(StoreCatalog::products(), 0, 4),
        ]);
    }

    public function shop(Request $request)
    {
        $category = $request->string('category')->toString() ?: null;
        $query = $request->string('q')->toString();
        $products = $query ? StoreCatalog::search($query) : StoreCatalog::byCategory($category);

        return view('store.shop', compact('products', 'category', 'query'));
    }

    public function product(string $id)
    {
        $product = StoreCatalog::find($id);
        abort_unless($product, 404);

        return view('store.product', ['product' => $product]);
    }

    public function services()
    {
        return view('store.services');
    }

    public function cart(Request $request)
    {
        $cart = collect($request->session()->get('cart', []));
        $items = $cart->map(fn ($item) => StoreCatalog::find($item['id']))->filter()->values();

        return view('store.cart', ['items' => $items]);
    }

    public function addToCart(Request $request, string $id)
    {
        abort_unless(StoreCatalog::find($id), 404);
        $cart = $request->session()->get('cart', []);
        $cart[] = ['id' => $id];
        $request->session()->put('cart', $cart);

        return redirect()->route('cart')->with('success', 'محصول به سبد خرید اضافه شد.');
    }

    public function serviceRequest(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:measurement,installation,design'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $requests = $request->session()->get('service_requests', []);
        $requests[] = $data + ['tracking' => 'PO-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8))];
        $request->session()->put('service_requests', $requests);

        return back()->with('service_success', 'درخواست شما ثبت شد و برای پیگیری آماده است.');
    }
}
