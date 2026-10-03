<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ServiceRequest;
use App\Support\StoreCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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

    public function visualizerProducts(Request $request)
    {
        $category = $request->string('category')->toString() ?: null;
        $allowed = ['carpet', 'laminate', 'spc'];
        abort_unless($category && in_array($category, $allowed, true), 422);

        $fallbackImages = [
            'carpet' => 'https://palazonline.com/storage/uploads/005-1-2.jpg',
            'laminate' => 'https://palazonline.com/storage/uploads/IMG_1100-4.PNG',
            'spc' => 'https://palazonline.com/storage/uploads/IMG_5777.PNG',
        ];

        $products = collect(StoreCatalog::byCategory($category))
            ->take(24)
            ->map(fn (array $product) => [
                'id' => $product['id'],
                'name' => $product['name'],
                'tone' => $product['tone'],
                'image' => $product['image'] ?: $fallbackImages[$category],
            ])
            ->values();

        return response()->json(['category' => $category, 'products' => $products]);
    }

    public function product(Request $request, string $id)
    {
        $directProduct = StoreCatalog::find($id);

        if ($directProduct) {
            $product = $directProduct;
            $variants = $product['model'] ? StoreCatalog::modelProducts($product['model']) : [$product];
        } else {
            $variants = StoreCatalog::modelProducts($id);
            abort_unless($variants !== [], 404);

            $selectedCode = $request->string('code')->toString();
            $product = StoreCatalog::findModelVariant($id, $selectedCode) ?? $variants[0];
        }

        return view('store.product', [
            'product' => $product,
            'variants' => $variants,
            'model' => $product['model'] ?? null,
        ]);
    }

    public function services(Request $request)
    {
        $selectedProduct = null;
        $productId = $request->string('product')->toString();

        if ($productId !== '') {
            $selectedProduct = StoreCatalog::find($productId);

            if (!$selectedProduct && $request->string('code')->isNotEmpty()) {
                $selectedProduct = StoreCatalog::findModelVariant(
                    $productId,
                    $request->string('code')->toString()
                );
            }
        }

        return view('store.services', compact('selectedProduct'));
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
                $product['roll_length'] = isset($item['roll_length']) ? max(1, min(15, (int) $item['roll_length'])) : null;
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
        $product = StoreCatalog::find($id);
        $rollLength = null;
        if (($product['calculation_type'] ?? null) === 'roll') {
            $rollLength = max(1, min(15, (int) $request->input('roll_length', 3)));
        }
        $cart = $request->session()->get('cart', []);
        $found = false;

        foreach ($cart as &$item) {
            if (($item['id'] ?? null) === $id) {
                $item['quantity'] = ($item['quantity'] ?? 1) + $quantity;
                if ($rollLength !== null) {
                    $item['roll_length'] = $rollLength;
                }
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            $cart[] = ['id' => $id, 'quantity' => $quantity, 'roll_length' => $rollLength];
        }

        $request->session()->put('cart', $cart);

        return redirect()->route('cart')->with('success', 'محصول به سبد خرید اضافه شد.');
    }

    public function removeFromCart(Request $request, string $id)
    {
        $cart = $request->session()->get('cart', []);
        $cart = array_values(array_filter($cart, fn ($item) => ($item['id'] ?? null) !== $id));
        $request->session()->put('cart', $cart);

        return redirect()->route('cart')->with('success', 'محصول از سبد خرید حذف شد.');
    }

    public function checkout(Request $request)
    {
        $items = $this->cartItems($request);

        if ($items->isEmpty()) {
            return redirect()->route('shop')->with('error', 'برای ادامه، ابتدا محصولی به سبد خرید اضافه کنید.');
        }

        $itemsTotal = $items->every(fn ($item) => $this->cartItemTotal($item) !== null)
            ? $items->sum(fn ($item) => $this->cartItemTotal($item))
            : null;

        return view('store.checkout', [
            'items' => $items,
            'itemsTotal' => $itemsTotal,
        ]);
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
            'installation_area' => ['nullable', 'numeric', 'min:0'],
            'installation_quantity' => ['nullable', 'numeric', 'min:0'],
            'installation_description' => ['nullable', 'string', 'max:1000'],
            'payment' => ['required', 'in:offline'],
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
                'subtotal' => $items->sum(fn ($item) => $this->cartItemTotal($item)),
                'total' => $items->sum(fn ($item) => $this->cartItemTotal($item)),
            ]);

            foreach ($items as $item) {
                $product = \App\Models\Product::where('slug', $item['id'])->where('is_active', true)->firstOrFail();
                $quantity = max(1, (int) $item['quantity']);
                $basePrice = $product->price;
                $rollLength = isset($item['roll_length']) ? max(1, min(15, (int) $item['roll_length'])) : null;
                $unitPrice = $basePrice;
                if ($rollLength !== null && $basePrice !== null) {
                    $unitPrice = $basePrice * 3 * $rollLength;
                }
                $lineTotal = $unitPrice !== null ? $unitPrice * $quantity : null;
                $productName = $product->name;
                if ($rollLength !== null) {
                    $productName .= ' — طاقه عرض ۳ × طول ' . $rollLength . ' متر';
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $productName,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            }

            return $order->load('items');
        });

        if (($data['service'] ?? 'none') === 'installation') {
            $prepareUrl = $this->forwardOrderInstallation($order, $data, $items);

            $request->session()->forget('cart');

            if ($prepareUrl) {
                return redirect()->away($prepareUrl);
            }
        }

        $request->session()->forget('cart');

        return view('store.order-success', ['order' => $order->fresh()]);
    }

    private function forwardOrderInstallation(Order $order, array $data, $items): ?string
    {
        $product = $items->first();

        $productSummary = $items->map(function (array $item) {
            return collect([
                $item['name'] ?? null,
                !empty($item['code']) ? 'کد: ' . $item['code'] : null,
                !empty($item['model']) ? 'مدل: ' . $item['model'] : null,
                isset($item['quantity']) ? 'تعداد: ' . $item['quantity'] : null,
            ])->filter()->implode(' | ');
        })->filter()->implode("\n");

        $installationDescription = trim((string) ($data['installation_description'] ?? ''));
        $description = collect([
            $productSummary ? 'محصولات سفارش:' . "\n" . $productSummary : null,
            $installationDescription ? 'توضیحات نصب: ' . $installationDescription : null,
        ])->filter()->implode("\n");

        $description = $description !== '' ? $description : null;

        $service = ServiceRequest::create([
            'order_id' => $order->id,
            'tracking_code' => $this->trackingCode('SR-', 8),
            'type' => 'installation',
            'name' => $data['name'],
            'phone' => $data['phone'],
            'description' => $description,
            'status' => 'received',
            'target_system' => 'dtz',
        ]);

        $token = (string) config('services.dtz.palaz_token');
        $url = rtrim((string) config('services.dtz.url'), '/') . '/api/palaz/installations';

        if ($token === '' || ! str_starts_with($url, 'http')) {
            $service->update(['status' => 'pending_integration']);
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(15)
                ->post($url, [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'city' => $data['city'],
                    'address' => $data['address'],
                    'product_code' => $product['code'] ?? null,
                    'product_title' => $product['name'] ?? null,
                    'product_model' => $product['model'] ?? null,
                    'area' => null,
                    'quantity' => null,
                    'description' => $description,
                    'palaz_order_id' => 'PO-' . $order->id,
                ]);

            if ($response->successful()) {
                $payload = $response->json();

                $service->update([
                    'status' => 'forwarded',
                    'external_id' => $payload['installation_id'] ?? null,
                ]);

                return $payload['prepare_url'] ?? null;
            }

            $service->update(['status' => 'integration_failed']);
        } catch (\Throwable $e) {
            report($e);
            $service->update(['status' => 'integration_failed']);
        }

        return null;
    }


    public function installationComplete(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'string', 'max:120'],
            'installation_id' => ['required', 'integer'],
            'tracking_code' => ['nullable', 'string', 'max:120'],
        ]);

        $orderId = $data['order_id'];
        $orderNumber = str_starts_with($orderId, 'PO-') ? (int) substr($orderId, 3) : 0;
        abort_unless($orderNumber > 0, 404);

        $order = Order::with('items')->findOrFail($orderNumber);
        abort_unless($order->service === 'installation', 404);

        $token = (string) config('services.dtz.palaz_token');
        $url = rtrim((string) config('services.dtz.url'), '/') . '/api/palaz/installations/' . (int) $data['installation_id'] . '/quote';
        abort_unless($token !== '' && str_starts_with($url, 'http'), 503);

        $response = Http::withToken($token)->acceptJson()->timeout(15)->get($url);
        abort_unless($response->successful(), 502);
        $quote = $response->json();
        abort_unless(($quote['success'] ?? false) && (int) ($quote['installation_id'] ?? 0) === (int) $data['installation_id'], 502);
        abort_unless(($quote['external_reference'] ?? null) === $orderId, 409);

        $installationAmount = (float) ($quote['total_amount'] ?? 0);
        $productsTotal = (float) $order->items->sum(fn ($item) => (float) ($item->line_total ?? 0));
        $order->update([
            'total' => $productsTotal + $installationAmount,
            'status' => 'received',
        ]);

        return view('store.order-success', [
            'order' => $order->fresh('items'),
            'installationAmount' => $installationAmount,
            'installationTrackingCode' => $quote['tracking_code'] ?? $data['tracking_code'] ?? null,
        ]);
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

        $apiKey = (string) config('services.openrouter.key');
        if ($apiKey !== '') {
            $payloadMessages = array_merge([
                [
                    'role' => 'system',
                    'content' => "تو مشاور هوشمند فروشگاه پالاز آنلاین هستی. تمام پاسخ‌های تو باید فقط و فقط به زبان فارسی و با خط فارسی باشند. اگر کاربر فارسی می‌نویسد، هرگز انگلیسی پاسخ نده و حتی سؤال‌های ساده را هم به انگلیسی ترجمه نکن. فقط نام برندها، مدل‌ها، کد محصولات یا اصطلاحات فنی که ذاتاً انگلیسی هستند می‌توانند به شکل اصلی خود باقی بمانند. فارسی، صمیمی، کوتاه و کاربردی پاسخ بده. نقش تو فروشنده صرف نیست؛ باید نیاز مشتری را مرحله‌ای کشف کنی. ترتیب پیشنهادی: کاربرد/فضا، متراژ، سبک یا اولویت، سپس محصول و پیشنهاد. در هر پیام فقط یک یا دو سؤال ضروری بپرس تا گفتگو طبیعی بماند. اگر کاربر اطلاعات کافی برای پیشنهاد دارد، پیشنهاد بده و دلیل کوتاه بیاور. فقط بر اساس کاتالوگ زیر درباره محصول و قیمت صحبت کن و هرگز قیمت یا مشخصات را حدس نزن. برای اندازه‌گیری، نصب و طراحی مسیر خدمات پالاز را معرفی کن. کاتالوگ فعلی:\n" . $catalog,
                ],
            ], $history);

            $payloadMessages[] = ['role' => 'user', 'content' => $data['message']];

            try {
                $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                    ->acceptJson()
                    ->withHeaders([
                        'HTTP-Referer' => config('app.url'),
                        'X-Title' => 'Palaz Online',
                    ])
                    ->timeout(30)
                    ->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model' => config('services.openrouter.model', 'openrouter/free'),
                        'messages' => $payloadMessages,
                        'max_tokens' => 500,
                    ]);

                if ($response->successful()) {
                    $text = trim((string) data_get($response->json(), 'choices.0.message.content', ''));

                    if ($text !== '') {
                        return response()->json([
                            'reply' => $text,
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
        // فقط پیام‌های کاربر وارد حافظه تحلیلی شوند؛ متن پاسخ‌های قبلی
        // نباید دوباره به‌عنوان «نیاز مشتری» تفسیر شوند.
        $userContext = collect($history)
            ->filter(fn (array $item) => ($item['role'] ?? null) === 'user')
            ->pluck('content')
            ->implode(' ');
        $context = mb_strtolower($userContext);
        $combined = trim($context . ' ' . $text);

        $area = $this->extractAdvisorArea($combined);
        $room = $this->detectAdvisorRoom($combined);
        $style = $this->detectAdvisorStyle($combined);
        $productTerm = $this->detectAdvisorProductTerm($combined);

        // وقتی کاربر یک فضای جدید را صریحاً می‌گوید، محصول قبلی را به این
        // پیام نچسبان؛ «پذیرایی» نباید به‌اشتباه «کفپوش ورزشی» تعبیر شود.
        $currentRoom = $this->detectAdvisorRoom($text);
        $currentProductTerm = $this->detectAdvisorProductTerm($text);
        if ($currentRoom !== null && $currentProductTerm === null) {
            $room = $currentRoom;
            $area = $this->extractAdvisorArea($text);
            $style = $this->detectAdvisorStyle($text);
            $productTerm = null;
        }

        $actions = $this->advisorActions($message);

        if ($this->isGreeting($text)) {
            return [
                'reply' => 'سلام 👋 من مشاور پالاز هستم. برای شروع، بگویید برای چه فضایی دنبال پوشش هستید؟',
                'actions' => [],
            ];
        }

        if ($room === null && $productTerm === null && !$this->isGeneralQuestion($text)) {
            return [
                'reply' => 'حتماً. اول بگویید برای کدام فضا می‌خواهید؟ مثلاً پذیرایی، اتاق خواب، دفتر یا فضای ورزشی.',
                'actions' => [],
            ];
        }

        if ($area === null && ($room !== null || $productTerm !== null)) {
            return [
                'reply' => 'خیلی خوب. حدود متراژ فضا چند متر است؟ متراژ تقریبی هم کافی است.',
                'actions' => $actions,
            ];
        }

        if (($room !== null || $productTerm !== null) && $area !== null && $style === null && $productTerm === null) {
            return [
                'reply' => 'عالی. حالا بگویید اولویت شما بیشتر کدام است: ظاهر و حس فضا، دوام و نظافت، یا قیمت مناسب؟',
                'actions' => $actions,
            ];
        }

        if ($productTerm !== null && $area !== null) {
            $productActions = $this->advisorProductActions($productTerm, 3);
            $names = collect($productActions)->pluck('label')->map(fn ($label) => preg_replace('/^مشاهده /u', '', $label))->filter()->values();

            $reply = 'برای ' . $productTerm . ' با متراژ حدود ' . $this->formatAdvisorNumber($area) . ' مترمربع، چند گزینه مرتبط از کاتالوگ پالاز را پیدا کردم.';
            if ($names->isNotEmpty()) {
                $reply .= ' گزینه‌ها: ' . $names->implode('، ') . '. اگر سبک یا بودجه‌تان را بگویید، بین این‌ها دقیق‌تر راهنمایی می‌کنم.';
            } else {
                $reply .= ' برای پیشنهاد دقیق‌تر، مدل یا سبک موردنظرتان را بگویید تا اطلاعات کاتالوگ را بررسی کنم.';
            }

            return [
                'reply' => $reply,
                'actions' => array_values(array_unique(array_merge($actions, $productActions), SORT_REGULAR)),
            ];
        }

        if ($room !== null && $area !== null && $style !== null && $productTerm === null) {
            return [
                'reply' => 'متوجه شدم: ' . $room . '، حدود ' . $this->formatAdvisorNumber($area) . ' مترمربع و اولویت «' . $style . '». حالا نوع پوشش را مشخص کنیم: موکت، لمینت، فرش‌گونه یا کاغذ دیواری؟',
                'actions' => [['label' => 'دیدن محصولات', 'url' => route('shop')]],
            ];
        }

        if (str_contains($text, 'قیمت') || str_contains($text, 'هزینه') || str_contains($text, 'محاسبه')) {
            return [
                'reply' => $productTerm
                    ? 'برای محاسبه دقیق ' . $productTerm . '، متراژ را بگویید. مثلاً ۶۰ مترمربع.'
                    : 'برای محاسبه دقیق، نام محصول و متراژ را بگویید؛ مثلاً «لمینت برای ۶۰ متر».',
                'actions' => [['label' => 'محاسبه و برآورد', 'url' => route('shop')]],
            ];
        }

        if (str_contains($text, 'اندازه') || str_contains($text, 'متراژ')) {
            return [
                'reply' => 'حتماً. اگر متراژ دقیق ندارید، می‌توانید درخواست اندازه‌گیری ثبت کنید.',
                'actions' => [['label' => 'درخواست اندازه‌گیری', 'url' => route('services')]],
            ];
        }

        if (str_contains($text, 'نصب') || str_contains($text, 'اجرا')) {
            return [
                'reply' => 'برای نصب و اجرا، درخواستتان را از مسیر خدمات ثبت کنید. نوع محصول و شهر را هم بگویید تا راهنمایی دقیق‌تری بدهم.',
                'actions' => [['label' => 'درخواست نصب', 'url' => route('services')]],
            ];
        }

        return [
            'reply' => 'برای اینکه دقیق راهنمایی‌تان کنم، نوع فضا، متراژ و نوع پوشش موردنظرتان را بگویید.',
            'actions' => $actions,
        ];
    }

    private function isGreeting(string $text): bool
    {
        return collect(['سلام', 'درود', 'خوبی', 'سلام وقت بخیر', 'وقت بخیر'])
            ->contains(fn ($word) => str_contains($text, $word));
    }

    private function detectAdvisorProductTerm(string $text): ?string
    {
        foreach ([
            'کاغذدیواری' => 'کاغذ دیواری',
            'کاغذ دیواری' => 'کاغذ دیواری',
            'لمینت' => 'لمینت',
            'فرش‌گونه' => 'فرش‌گونه',
            'فرش' => 'فرش‌گونه',
            'موکت' => 'موکت',
            'کفپوش ورزشی' => 'کفپوش ورزشی',
            'ورزشی' => 'کفپوش ورزشی',
            'پادری' => 'پادری',
        ] as $needle => $label) {
            if (str_contains($text, $needle)) {
                return $label;
            }
        }

        return null;
    }

    private function advisorProductActions(string $productTerm, int $limit = 3): array
    {
        $products = StoreCatalog::products();
        $termMap = [
            'کاغذ دیواری' => ['wallpaper', 'کاغذ'],
            'لمینت' => ['laminate', 'لمینت'],
            'فرش‌گونه' => ['spc', 'فرش'],
            'موکت' => ['carpet', 'موکت'],
            'کفپوش ورزشی' => ['carpet-tile', 'ورزشی'],
            'پادری' => ['decorative', 'پادری'],
        ];
        $matches = $termMap[$productTerm] ?? [$productTerm];

        return collect($products)
            ->filter(function ($product) use ($matches) {
                $haystack = mb_strtolower(
                    ($product['name'] ?? '') . ' ' .
                    ($product['category'] ?? '') . ' ' .
                    ($product['description'] ?? '')
                );

                return collect($matches)->contains(fn ($match) => str_contains($haystack, mb_strtolower($match)));
            })
            ->take($limit)
            ->map(fn ($product) => [
                'label' => 'مشاهده ' . $product['name'],
                'url' => route('product', ['id' => $product['id']]),
            ])
            ->values()
            ->all();
    }

    private function formatAdvisorNumber(float $number): string
    {
        return rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.');
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
                $actions[] = ['label' => 'مشاهده ' . $product['name'], 'url' => route('product', ['id' => $product['id']])];
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
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'product_id' => ['nullable', 'string', 'max:120'],
            'product_code' => ['nullable', 'string', 'max:100'],
            'product_title' => ['nullable', 'string', 'max:255'],
            'product_model' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $target = match ($data['type']) {
            'measurement', 'installation' => 'dtz',
            'design' => 'dtz_tablet',
        };

        $description = collect([
            $data['product_title'] ? 'محصول: ' . $data['product_title'] : null,
            $data['product_model'] ? 'مدل: ' . $data['product_model'] : null,
            $data['product_code'] ? 'کد: ' . $data['product_code'] : null,
            isset($data['area']) ? 'متراژ: ' . $data['area'] . ' مترمربع' : null,
            isset($data['quantity']) ? 'تعداد: ' . $data['quantity'] : null,
            'شهر: ' . $data['city'],
            'آدرس: ' . $data['address'],
            $data['description'] ?? null,
        ])->filter()->implode("\n");

        $service = ServiceRequest::create([
            'tracking_code' => $this->trackingCode('SR-', 8),
            'type' => $data['type'],
            'name' => $data['name'],
            'phone' => $data['phone'],
            'description' => $description ?: null,
            'status' => 'received',
            'target_system' => $target,
        ]);

        if ($data['type'] === 'installation') {
            $token = (string) config('services.dtz.palaz_token');
            $url = rtrim((string) config('services.dtz.url'), '/') . '/api/palaz/installations';

            if ($token === '' || ! str_starts_with($url, 'http')) {
                $service->update(['status' => 'pending_integration']);
                return back()->withInput()->with('service_error', 'مسیر اتصال خدمات نصب هنوز تنظیم نشده است.');
            }

            try {
                $response = Http::withToken($token)
                    ->acceptJson()
                    ->timeout(15)
                    ->post($url, [
                        'name' => $data['name'],
                        'phone' => $data['phone'],
                        'city' => $data['city'],
                        'address' => $data['address'],
                        'product_code' => $data['product_code'] ?? null,
                        'product_title' => $data['product_title'] ?? null,
                        'product_model' => $data['product_model'] ?? null,
                        'area' => $data['area'] ?? null,
                        'quantity' => $data['quantity'] ?? null,
                        'description' => $data['description'] ?? null,
                        'palaz_order_id' => 'SR-' . $service->id,
                    ]);

                if ($response->successful()) {
                    $payload = $response->json();
                    $service->update([
                        'status' => 'forwarded',
                        'external_id' => $payload['installation_id'] ?? null,
                    ]);

                    return back()->with(
                        'service_success',
                        'درخواست نصب ثبت شد. کد پیگیری: ' . ($payload['tracking_code'] ?? $service->tracking_code)
                    );
                }

                $service->update(['status' => 'integration_failed']);
            } catch (\Throwable $e) {
                report($e);
                $service->update(['status' => 'integration_failed']);
            }

            return back()->withInput()->with(
                'service_error',
                'درخواست ثبت شد اما اتصال به سامانه نصب برقرار نشد. لطفاً دوباره تلاش کنید.'
            );
        }

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
                $product['roll_length'] = isset($item['roll_length']) ? max(1, min(15, (int) $item['roll_length'])) : null;
                return $product;
            })
            ->filter()
            ->values();
    }

    private function cartItemTotal(array $item): ?float
    {
        if (($item['price'] ?? null) === null) return null;
        $quantity = max(1, (int) ($item['quantity'] ?? 1));
        $price = (float) $item['price'];
        if (($item['calculation_type'] ?? null) === 'roll' && !empty($item['roll_length'])) {
            $price *= 3 * max(1, min(15, (int) $item['roll_length']));
        }
        return $price * $quantity;
    }

    private function trackingCode(string $prefix, int $length): string
    {
        do {
            $code = $prefix . strtoupper(substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length));
        } while (Order::where('tracking_code', $code)->exists() || ServiceRequest::where('tracking_code', $code)->exists());

        return $code;
    }
}
