<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ServiceRequest;
use App\Support\StoreCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $cart = $request->session()->get('cart', []);
        $items = collect($cart)
            ->map(function ($item) {
                $product = StoreCatalog::find($item['id']);
                if (!$product) {
                    return null;
                }

                $product['quantity'] = max(1, (int) ($item['quantity'] ?? 1));
                return $product;
            })
            ->filter()
            ->values();

        return view('store.cart', ['items' => $items]);
    }

    public function addToCart(Request $request, string $id)
    {
        abort_unless(StoreCatalog::find($id), 404);

        $quantity = max(1, (int) $request->input('quantity', 1));
        $cart = $request->session()->get('cart', []);
        $found = false;

        foreach ($cart as &$item) {
            if (($item['id'] ?? null) === $id) {
                $item['quantity'] = ($item['quantity'] ?? 1) + $quantity;
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            $cart[] = ['id' => $id, 'quantity' => $quantity];
        }

        $request->session()->put('cart', $cart);

        return redirect()->route('cart')->with('success', 'محصول به سبد خرید اضافه شد.');
    }

    public function checkout(Request $request)
    {
        $items = $this->cartItems($request);

        if ($items->isEmpty()) {
            return redirect()->route('shop')->with('error', 'برای ادامه، ابتدا محصولی به سبد خرید اضافه کنید.');
        }

        return view('store.checkout', ['items' => $items]);
    }

    public function placeOrder(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'service' => ['nullable', 'in:none,measurement,installation,design'],
            'payment' => ['required', 'in:pending,offline'],
        ]);

        $items = $this->cartItems($request);
        if ($items->isEmpty()) {
            return redirect()->route('shop')->with('error', 'سبد خرید شما خالی است.');
        }

        $order = DB::transaction(function () use ($data, $items) {
            $order = Order::create([
                'tracking_code' => $this->trackingCode('PO-', 10),
                'name' => $data['name'],
                'phone' => $data['phone'],
                'city' => $data['city'],
                'postal_code' => $data['postal_code'] ?? null,
                'address' => $data['address'],
                'service' => $data['service'] ?? 'none',
                'payment_status' => $data['payment'],
                'status' => 'received',
                'subtotal' => 0,
                'total' => 0,
            ]);

            foreach ($items as $item) {
                $product = \App\Models\Product::where('slug', $item['id'])->where('is_active', true)->firstOrFail();
                $quantity = max(1, (int) $item['quantity']);
                $unitPrice = $product->price;
                $lineTotal = $unitPrice !== null ? $unitPrice * $quantity : null;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            }

            return $order->load('items');
        });

        $request->session()->forget('cart');

        return view('store.order-success', ['order' => $order]);
    }

    public function serviceRequest(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:measurement,installation,design'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $target = match ($data['type']) {
            'measurement', 'installation' => 'dtz',
            'design' => 'dtz_tablet',
        };

        $service = ServiceRequest::create([
            'tracking_code' => $this->trackingCode('SR-', 8),
            'type' => $data['type'],
            'name' => $data['name'],
            'phone' => $data['phone'],
            'description' => $data['description'] ?? null,
            'status' => 'received',
            'target_system' => $target,
        ]);

        return back()->with('service_success', 'درخواست شما ثبت شد. کد پیگیری: ' . $service->tracking_code);
    }

    private function cartItems(Request $request)
    {
        return collect($request->session()->get('cart', []))
            ->map(function ($item) {
                $product = StoreCatalog::find($item['id'] ?? '');
                if (!$product) {
                    return null;
                }

                $product['quantity'] = max(1, (int) ($item['quantity'] ?? 1));
                return $product;
            })
            ->filter()
            ->values();
    }

    private function trackingCode(string $prefix, int $length): string
    {
        do {
            $code = $prefix . strtoupper(substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length));
        } while (Order::where('tracking_code', $code)->exists() || ServiceRequest::where('tracking_code', $code)->exists());

        return $code;
    }
}
