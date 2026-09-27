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

    public function advisorChat(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1200'],
            'messages' => ['nullable', 'array', 'max:12'],
            'messages.*.role' => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:1200'],
        ]);

        $catalog = collect(StoreCatalog::products())
            ->map(fn (array $product) => implode(' | ', array_filter([
                'نام: ' . $product['name'],
                'دسته: ' . ($product['category'] ?? ''),
                'قیمت: ' . ($product['price'] !== null ? number_format((float) $product['price']) : 'استعلامی'),
                'واحد: ' . ($product['unit'] ?? ''),
                'توضیح: ' . ($product['description'] ?? ''),
            ])))
            ->take(80)
            ->implode("\n");

        $history = collect($data['messages'] ?? [])
            ->map(fn (array $message) => [
                'role' => $message['role'],
                'content' => $message['content'],
            ])
            ->values()
            ->all();

        $apiKey = (string) config('services.openai.key');
        if ($apiKey !== '') {
            $payloadMessages = array_merge([
                [
                    'role' => 'system',
                    'content' => "تو مشاور هوشمند فروشگاه پالاز آنلاین هستی. فارسی، صمیمی، کوتاه و کاربردی پاسخ بده. نقش تو فروشنده صرف نیست؛ باید نیاز مشتری را مرحله‌ای کشف کنی. ترتیب پیشنهادی: کاربرد/فضا، متراژ، سبک یا اولویت، سپس محصول و پیشنهاد. در هر پیام فقط یک یا دو سؤال ضروری بپرس تا گفتگو طبیعی بماند. اگر کاربر اطلاعات کافی برای پیشنهاد دارد، پیشنهاد بده و دلیل کوتاه بیاور. فقط بر اساس کاتالوگ زیر درباره محصول و قیمت صحبت کن و هرگز قیمت یا مشخصات را حدس نزن. برای اندازه‌گیری، نصب و طراحی مسیر خدمات پالاز را معرفی کن. کاتالوگ فعلی:\n" . $catalog,
                ],
            ], $history);

            $payloadMessages[] = ['role' => 'user', 'content' => $data['message']];

            try {
                $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                    ->acceptJson()
                    ->timeout(20)
                    ->post('https://api.openai.com/v1/responses', [
                        'model' => config('services.openai.model', 'gpt-5.6-luna'),
                        'input' => $payloadMessages,
                        'max_output_tokens' => 500,
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $text = collect($json['output'] ?? [])
                        ->flatMap(fn ($item) => $item['content'] ?? [])
                        ->pluck('text')
                        ->filter()
                        ->implode("\n");

                    if ($text !== '') {
                        return response()->json([
                            'reply' => trim($text),
                            'actions' => $this->advisorActions($data['message']),
                            'mode' => 'ai',
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $fallback = $this->advisorFallback($data['message'], $history);

        return response()->json([
            'reply' => $fallback['reply'],
            'actions' => $fallback['actions'],
            'mode' => 'catalog',
        ]);
    }

    private function advisorFallback(string $message, array $history = []): array
    {
        $text = mb_strtolower(trim($message));
        $actions = $this->advisorActions($message);
        $products = StoreCatalog::products();

        $context = mb_strtolower(collect($history)
            ->pluck('content')
            ->implode(' '));

        $area = $this->extractAdvisorArea($text . ' ' . $context);
        $room = $this->detectAdvisorRoom($text . ' ' . $context);
        $style = $this->detectAdvisorStyle($text . ' ' . $context);
        $hasProduct = $this->detectAdvisorProduct($text . ' ' . $context);

        if ($area === null && !$this->isGeneralQuestion($text) && !$hasProduct) {
            return [
                'reply' => 'حتماً. اول بگویید این پوشش را برای کدام فضا می‌خواهید؟ مثلاً پذیرایی، اتاق خواب، دفتر یا فضای ورزشی.',
                'actions' => $actions,
            ];
        }

        if ($area === null && ($room !== null || $hasProduct)) {
            return [
                'reply' => 'خیلی خوب. حدود متراژ فضا چند متر است؟ اگر دقیق نمی‌دانید، یک عدد تقریبی هم کافی است.',
                'actions' => $actions,
            ];
        }

        if ($style === null && $room !== null && !$hasProduct) {
            return [
                'reply' => 'متوجه شدم. برای این فضا بیشتر چه چیزی برایتان مهم است: ظاهر و حس فضا، دوام و نظافت، یا قیمت مناسب؟',
                'actions' => $actions,
            ];
        }

        if ($hasProduct || ($room !== null && $area !== null)) {
            $sizeText = $area !== null ? ' برای حدود ' . $area . ' مترمربع' : '';
            $roomText = $room !== null ? ' در ' . $room : '';
            return [
                'reply' => 'عالیه.' . $roomText . $sizeText . ' حالا می‌توانیم گزینه‌های مناسب را بررسی کنیم. اگر سبک یا اولویت‌تان را بگویید، پیشنهاد را دقیق‌تر می‌کنم؛ اگر هم محصول مشخصی مدنظر دارید، همان را بررسی کنیم.',
                'actions' => $actions,
            ];
        }

        $keywords = [
            'کاغذ دیواری' => ['wallpaper', 'کاغذ'],
            'لمینت' => ['laminate', 'لمینت'],
            'فرش' => ['spc', 'فرش'],
            'فرش‌گونه' => ['spc', 'فرش'],
            'موکت' => ['carpet', 'موکت'],
            'ورزشی' => ['carpet-tile', 'ورزشی'],
        ];

        foreach ($keywords as $term => $matches) {
            if (str_contains($text, $term)) {
                foreach ($products as $product) {
                    $haystack = mb_strtolower(($product['name'] ?? '') . ' ' . ($product['category'] ?? '') . ' ' . ($product['description'] ?? ''));
                    if (collect($matches)->contains(fn ($match) => str_contains($haystack, $match))) {
                        $actions[] = [
                            'label' => 'مشاهده ' . $product['name'],
                            'url' => route('product', ['id' => $product['id']]),
                        ];
                        if (count($actions) >= 3) break;
                    }
                }
                break;
            }
        }

        if (str_contains($text, 'قیمت') || str_contains($text, 'هزینه') || str_contains($text, 'محاسبه')) {
            $reply = 'حتماً. برای محاسبه دقیق، نام محصول و متراژ فضا را بگویید. اگر متراژ ندارید، می‌توانیم از مسیر اندازه‌گیری شروع کنیم.';
            $actions[] = ['label' => 'محاسبه و برآورد', 'url' => route('shop')];
        } elseif (str_contains($text, 'اندازه') || str_contains($text, 'متراژ')) {
            $reply = 'برای اندازه‌گیری، درخواست شما می‌تواند از مسیر خدمات پالاز ثبت شود. اگر متراژ تقریبی دارید، بگویید تا انتخاب محصول را هم دقیق‌تر کنیم.';
            $actions[] = ['label' => 'درخواست اندازه‌گیری', 'url' => route('services')];
        } elseif (str_contains($text, 'نصب') || str_contains($text, 'اجرا')) {
            $reply = 'برای نصب و اجرا می‌توانیم درخواست شما را وارد مسیر خدمات پالاز کنیم. نوع محصول و شهر را هم بگویید.';
            $actions[] = ['label' => 'درخواست نصب', 'url' => route('services')];
        } elseif (str_contains($text, 'پذیرایی') || str_contains($text, 'اتاق') || str_contains($text, 'خواب')) {
            $reply = 'برای پیشنهاد مناسب، متراژ تقریبی، کاربرد فضا و سبک مورد علاقه‌تان را بگویید؛ مثلاً مدرن، گرم، مینیمال یا کلاسیک.';
            $actions[] = ['label' => 'دیدن محصولات', 'url' => route('shop')];
        } elseif (empty($actions)) {
            $reply = 'در خدمتم. برای اینکه مثل یک مشاور واقعی راهنمایی‌تان کنم، بگویید فضای شما کجاست، حدوداً چند متر است و دنبال چه نوع پوششی هستید.';
        } else {
            $reply = 'چند گزینه مرتبط از کاتالوگ پالاز پیدا کردم. اگر متراژ و کاربرد فضا را بگویید، پیشنهاد را دقیق‌تر می‌کنم.';
        }

        return ['reply' => $reply, 'actions' => array_values(array_unique($actions, SORT_REGULAR))];
    }

    private function advisorActions(string $message): array
    {
        $text = mb_strtolower(trim($message));
        $actions = [];

        if (str_contains($text, 'اندازه')) {
            $actions[] = ['label' => 'درخواست اندازه‌گیری', 'url' => route('services')];
        }
        if (str_contains($text, 'نصب') || str_contains($text, 'اجرا')) {
            $actions[] = ['label' => 'درخواست نصب', 'url' => route('services')];
        }
        if (str_contains($text, 'قیمت') || str_contains($text, 'هزینه') || str_contains($text, 'محاسبه')) {
            $actions[] = ['label' => 'محاسبه و برآورد', 'url' => route('shop')];
        }

        foreach (StoreCatalog::products() as $product) {
            $haystack = mb_strtolower(($product['name'] ?? '') . ' ' . ($product['description'] ?? ''));
            if ($product['name'] && str_contains($text, mb_strtolower($product['name']))) {
                $actions[] = ['label' => 'مشاهده ' . $product['name'], 'url' => route('product', ['id' => $product['id']])];
            } elseif (str_contains($text, 'لمینت') && str_contains($haystack, 'laminate')) {
                $actions[] = ['label' => 'مشاهده ' . $product['name'], 'url' => route('product', ['id' => $product['id']]);
            }
            if (count($actions) >= 3) break;
        }

        return array_values(array_unique($actions, SORT_REGULAR));
    }

    private function extractAdvisorArea(string $text): ?float
    {
        if (preg_match('/(?:حدود|تقریباً|تقریبا)?\s*(\d+(?:[\.,]\d+)?)\s*(?:متر|متری|مترمربع|متر مربع)/u', $text, $m)) {
            return (float) str_replace(',', '.', $m[1]);
        }
        return null;
    }

    private function detectAdvisorRoom(string $text): ?string
    {
        foreach (['پذیرایی', 'اتاق خواب', 'اتاق', 'دفتر', 'راهرو', 'فروشگاه', 'فضای ورزشی'] as $room) {
            if (str_contains($text, $room)) return $room;
        }
        return null;
    }

    private function detectAdvisorStyle(string $text): ?string
    {
        foreach (['مدرن', 'مینیمال', 'کلاسیک', 'گرم', 'اقتصادی', 'بادوام', 'قابل شستشو', 'نظافت'] as $style) {
            if (str_contains($text, $style)) return $style;
        }
        return null;
    }

    private function detectAdvisorProduct(string $text): bool
    {
        return collect(['موکت', 'لمینت', 'فرش', 'فرش‌گونه', 'کاغذ دیواری', 'کاغذدیواری', 'کفپوش ورزشی', 'پادری'])
            ->contains(fn ($word) => str_contains($text, $word));
    }

    private function isGeneralQuestion(string $text): bool
    {
        return collect(['قیمت', 'هزینه', 'محاسبه', 'اندازه', 'نصب', 'اجرا', 'مقایسه', 'محصول'])
            ->contains(fn ($word) => str_contains($text, $word));
    }

    private function advisorFallbackReply(string $message, string $catalog): string
    {
        $text = mb_strtolower(trim($message));

        if (str_contains($text, 'قیمت') || str_contains($text, 'هزینه') || str_contains($text, 'محاسبه')) {
            return 'حتماً. برای قیمت دقیق، نام محصول و متراژ فضا را بگویید. اگر اندازه دقیق ندارید، می‌توانیم ابتدا درخواست اندازه‌گیری ثبت کنیم.';
        }

        if (str_contains($text, 'اندازه') || str_contains($text, 'متراژ')) {
            return 'برای اندازه‌گیری، می‌توانیم درخواست شما را ثبت کنیم تا مسیر اندازه‌گیری و اجرای پالاز ادامه پیدا کند. اگر متراژ تقریبی را دارید، همان را هم بگویید.';
        }

        if (str_contains($text, 'نصب') || str_contains($text, 'اجرا')) {
            return 'برای نصب و اجرا می‌توانید درخواست نصب ثبت کنید. اگر نوع محصول و شهر را بگویید، راهنمایی دقیق‌تری می‌دهم.';
        }

        if (str_contains($text, 'پذیرایی') || str_contains($text, 'اتاق') || str_contains($text, 'خواب')) {
            return 'برای پیشنهاد دقیق، کاربرد فضا، متراژ تقریبی و سبک مورد علاقه‌تان را بگویید؛ مثلاً مدرن، گرم، مینیمال یا کلاسیک.';
        }

        if (str_contains($text, 'موکت') || str_contains($text, 'فرش') || str_contains($text, 'لمینت') || str_contains($text, 'کاغذ دیواری')) {
            return 'حتماً. نوع محصول، متراژ و کاربرد فضا را بگویید تا از بین اطلاعات کاتالوگ پالاز گزینه‌های مرتبط را بررسی کنیم.';
        }

        return 'در خدمتم. درباره انتخاب محصول، مقایسه، قیمت و محاسبه، اندازه‌گیری یا نصب سؤال کنید. اگر نام محصول یا متراژ را هم بگویید، پاسخ دقیق‌تر می‌شود.';
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
