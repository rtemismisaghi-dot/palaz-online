<?php

namespace App\Agents;

use App\Support\StoreCatalog;
use Illuminate\Support\Facades\Http;

final class PalazAdvisorAgent
{
    public function reply(string $message, array $history = [], array $context = []): array
    {
        $message = trim($message);
        $history = collect($history)
            ->filter(fn ($m) => in_array($m['role'] ?? null, ['user', 'assistant'], true))
            ->map(fn ($m) => ['role' => $m['role'], 'content' => mb_substr((string) ($m['content'] ?? ''), 0, 1200)])
            ->take(-10)->values()->all();

        $allProducts = collect(StoreCatalog::products());
        $catalog = $allProducts
            ->map(fn ($p) => implode(' | ', array_filter([
                'شناسه: '.($p['id'] ?? ''),
                'نام: '.$p['name'],
                'دسته: '.($p['category'] ?? ''),
                'مدل/آلبوم: '.($p['model'] ?? ''),
                'کد: '.($p['code'] ?? ''),
                'قیمت پایه: '.($p['price'] !== null ? number_format((float)$p['price']).' تومان' : 'استعلامی'),
                'واحد: '.($p['unit'] ?? ''),
                'رنگ/تون: '.($p['tone'] ?? ''),
                'توضیح: '.($p['description'] ?? ''),
            ])))->take(250)->implode("\n");

        // قبل از ارسال به مدل، محصولات مرتبط با متن کاربر را از کاتالوگ واقعی پیدا می‌کنیم
        // تا مشاور مجبور نباشد بین کل کاتالوگ حدس بزند.
        $matchedProducts = $this->matchProducts($message, $allProducts);
        $matchedText = $matchedProducts->isNotEmpty()
            ? "\n\nمحصولات مرتبط با پیام فعلی کاربر از کاتالوگ واقعی:\n"
                .$matchedProducts->map(fn ($p) => implode(' | ', array_filter([
                    'شناسه: '.($p['id'] ?? ''),
                    'نام: '.($p['name'] ?? ''),
                    'دسته: '.($p['category'] ?? ''),
                    'مدل/آلبوم: '.($p['model'] ?? ''),
                    'کد: '.($p['code'] ?? ''),
                    'قیمت پایه: '.($p['price'] !== null ? number_format((float)$p['price']).' تومان' : 'استعلامی'),
                    'واحد: '.($p['unit'] ?? ''),
                    'رنگ/تون: '.($p['tone'] ?? ''),
                ])))->implode("\n")
            : '';

        $apiKey = (string) config('services.openrouter.key');

        if ($apiKey !== '') {
            $system = <<<'PROMPT'
تو «مشاور هوشمند پالاز» هستی؛ یک مشاور فارسی‌زبان برای انتخاب، مقایسه و برنامه‌ریزی خرید و اجرای محصولات پالاز.
لحن: صمیمی، حرفه‌ای، کوتاه و طبیعی؛ فروشنده اصراری نباش.
اول نیاز مشتری را بفهم: فضا، متراژ، سبک/اولویت، بودجه و محدودیت‌ها. در هر نوبت حداکثر دو سؤال ضروری بپرس.
فقط درباره محصول و قیمت از کاتالوگ داده‌شده استفاده کن و هرگز قیمت، موجودی یا مشخصات را حدس نزن.
مهم: زبان خروجی همیشه فارسی باشد. حتی اگر پیام کاربر با صدا تشخیص داده شده، پاسخ را فارسی و با خط فارسی تولید کن؛ از انگلیسی استفاده نکن مگر برای نام خاص محصول، برند یا کد.
اگر اطلاعات کافی است، 2 تا 3 گزینه را با دلیل کوتاه مقایسه کن.
اگر کاربر در Visualizer دو محصول را مقایسه می‌کند، همان دو محصول را مبنای پاسخ قرار بده و تفاوت‌ها را فقط بر اساس اطلاعات کاتالوگ توضیح بده؛ محصولی را به‌عنوان «بهترین» یا «برنده» اعلام نکن.
اگر کاربر درباره اندازه‌گیری، نصب، طراحی یا محاسبه پرسید، مسیر خدمات پالاز را پیشنهاد کن.
همیشه فارسی پاسخ بده مگر نام محصول/برند/کد ذاتاً لاتین باشد.
اگر سؤال خارج از حوزه پالاز است ولی پاسخ عمومی و قابل‌اعتمادش را می‌دانی، پاسخ مفید و کوتاه بده و اگر برای آن حوزه تخصصی نیستی این محدودیت را شفاف بگو؛ گفتگو را بی‌دلیل قطع نکن. برای موضوعات حساس یا نیازمند اطلاعات به‌روز، ادعای قطعی نکن و کاربر را به منبع مناسب راهنمایی کن.
کاتالوگ فعلی:
PROMPT;
            $contextText = $this->contextText($context).$matchedText;
            $payload = array_merge(
                [['role' => 'system', 'content' => $system."\n".$catalog.$contextText]],
                $history,
                [['role' => 'user', 'content' => $message]]
            );

            try {
                $response = Http::withToken($apiKey)
                    ->acceptJson()
                    ->withHeaders([
                        'HTTP-Referer' => config('app.url'),
                        'X-Title' => 'Palaz AI Advisor',
                    ])
                    ->timeout(30)
                    ->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model' => config('services.openrouter.model', 'openrouter/free'),
                        'messages' => $payload,
                        'max_tokens' => 650,
                        'temperature' => 0.45,
                    ]);

                $text = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
                if ($response->successful() && $text !== '') {
                    // بعضی مدل‌های رایگان OpenRouter ممکن است با وجود دستور سیستم، پاسخ لاتین بدهند.
                    // اگر پاسخ عمدتاً فارسی نیست، همان سرویس را برای تبدیل قطعی به فارسی دوباره صدا می‌زنیم.
                    if ($this->isMostlyLatin($text)) {
                        $translation = Http::withToken($apiKey)
                            ->acceptJson()
                            ->withHeaders([
                                'HTTP-Referer' => config('app.url'),
                                'X-Title' => 'Palaz AI Advisor Persian',
                            ])
                            ->timeout(30)
                            ->post('https://openrouter.ai/api/v1/chat/completions', [
                                'model' => config('services.openrouter.model', 'openrouter/free'),
                                'messages' => [
                                    ['role' => 'system', 'content' => 'فقط مترجم فارسی هستی. متن زیر را به فارسی روان و طبیعی ترجمه کن. هیچ توضیح اضافه، انگلیسی یا Markdown نده. نام برند، محصول و کد را در صورت نیاز لاتین نگه دار.'],
                                    ['role' => 'user', 'content' => $text],
                                ],
                                'max_tokens' => 650,
                                'temperature' => 0.2,
                            ]);
                        $translated = trim((string) data_get($translation->json(), 'choices.0.message.content', ''));
                        if ($translation->successful() && $translated !== '') {
                            $text = $translated;
                        }
                    }

                    return ['reply' => $text, 'mode' => 'ai', 'actions' => $this->actions($message)];
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return ['reply' => $this->fallback($message), 'mode' => 'catalog', 'actions' => $this->actions($message)];
    }

    public function analyzeSpace(string $imageDataUrl): array
    {
        $apiKey = (string) config('services.openrouter.key');
        if ($apiKey === '') {
            return ['ok' => false, 'message' => 'تحلیل تصویری فعلاً فعال نیست؛ کلید سرویس هوش مصنوعی تنظیم نشده است.', 'floor_polygon' => null];
        }

        $system = <<<'PROMPT'
تو یک موتور حرفه‌ای Vision برای Room Visualizer پالاز هستی. عکس را مثل یک طراح داخلی و سیستم segmentation بررسی کن و فقط سطح واقعی و قابل‌مشاهده کف را برای پوشاندن با کفپوش مشخص کن.
پاسخ را فقط به صورت JSON معتبر و بدون Markdown بده:
{"floor_polygon":[[x,y],...],"confidence":0,"floor_notes":"..."}
قواعد:
- x و y درصدی بین 0 تا 100 هستند.
- نقاط را به ترتیب دور تمام گوشه‌ها و شکست‌های مرز کف بده؛ برای کف‌های پرسپکتیو حداقل 8 و حداکثر 30 نقطه بده و مرز را تا حد ممکن دقیق دنبال کن.
- اگر کف کاملاً دیده نمی‌شود، نزدیک‌ترین محدوده قابل‌اعتماد را مشخص کن.
- مبلمان، تخت، میز، صندلی، فرش/قالی، پله، دیوار، پرده و سقف را حتی اگر روی کف قرار گرفته‌اند داخل ماسک نیاور.
- اگر کف پشت یا زیر مبلمان دیده نمی‌شود، آن قسمت را حدس نزن.
- خطوط اتصال کف با دیوار و پایه مبلمان را به‌عنوان مرز واقعی در نظر بگیر.
- شکل کف را با پرسپکتیو تصویر دنبال کن؛ یک ذوزنقه ساده فقط وقتی مجاز است که واقعاً مرز کف همین شکل باشد.
- اگر چند بخش جدا از کف وجود دارد، همه بخش‌های قابل‌مشاهده را در یک polygon پیوسته تقریبی پوشش بده و بخش‌های غیرکف را وارد نکن.
- اول کف را از سایر سطوح جدا کن، سپس نقاط مرزی را انتخاب کن.
- confidence عددی بین 0 و 1 باشد.
- توضیح کوتاه فارسی باشد.
PROMPT;

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->withHeaders(['HTTP-Referer' => config('app.url'), 'X-Title' => 'Palaz AI Advisor Vision'])
                ->timeout(45)
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => config('services.openrouter.vision_model', config('services.openrouter.model', 'openrouter/free')),
                    'messages' => [[
                        'role' => 'system',
                        'content' => $system,
                    ], [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => 'این عکس فضای کاربر است. سطح کف را برای Visualizer پیدا کن.'],
                            ['type' => 'image_url', 'image_url' => ['url' => $imageDataUrl]],
                        ],
                    ]],
                    'max_tokens' => 900,
                    'temperature' => 0.1,
                ]);

            $text = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
            $text = preg_replace('/^```(?:json)?\s*|\s*```$/u', '', $text);
            $data = json_decode($text, true);
            $polygon = $data['floor_polygon'] ?? null;

            if ($response->successful() && is_array($polygon) && count($polygon) >= 4) {
                $clean = collect($polygon)->map(function ($point) {
                    return [max(0, min(100, (float) ($point[0] ?? 0))), max(0, min(100, (float) ($point[1] ?? 0)))];
                })->values()->all();

                // بررسی دوم، همان تصویر را با polygon اولیه دوباره به Vision می‌دهد تا
                // مرزهای پرسپکتیو، مبلمان و اتصال کف/دیوار دقیق‌تر بازبینی شوند.
                $reviewSystem = <<<'REVIEW'
تو بازبین نهایی ماسک کف برای Room Visualizer هستی.
عکس و polygon اولیه را بررسی کن و فقط نسخه دقیق‌تر مرز واقعی کف را برگردان.
فقط JSON معتبر و بدون Markdown:
{"floor_polygon":[[x,y],...],"confidence":0,"floor_notes":""}
- مختصات 0 تا 100 و مربوط به خود تصویر باشند.
- مرز کف را دقیق روی اتصال کف با دیوار، قرنیز، پایه مبلمان و لبه پله دنبال کن.
- مبلمان، تخت، میز، صندلی، کمد، فرش، قالی و پادری را داخل ماسک نیاور.
- زیر یا پشت اجسام را حدس نزن؛ فقط کف قابل مشاهده را ماسک کن.
- دیوار، سقف، پرده و سطوح عمودی را حذف کن.
- ذوزنقه ساده فقط اگر واقعاً با مرز تصویر منطبق است؛ در غیر این صورت از شکست‌های متعدد استفاده کن.
- حداقل 10 نقطه و برای مرزهای پیچیده تا 40 نقطه بده.
- polygon اولیه را صرفاً تأیید نکن؛ اگر اشتباه است اصلاحش کن.
- confidence بین 0 و 1 باشد.
REVIEW;
                $reviewPrompt = 'Polygon اولیه: '.json_encode($clean, JSON_UNESCAPED_UNICODE)."\nآن را با خود تصویر تطبیق بده و نسخه دقیق‌تر را برگردان.";
                $review = Http::withToken($apiKey)
                    ->acceptJson()
                    ->withHeaders(['HTTP-Referer' => config('app.url'), 'X-Title' => 'Palaz AI Floor Review'])
                    ->timeout(45)
                    ->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model' => config('services.openrouter.vision_model', config('services.openrouter.model', 'openrouter/free')),
                        'messages' => [[
                            'role' => 'system',
                            'content' => $reviewSystem,
                        ], [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $reviewPrompt],
                                ['type' => 'image_url', 'image_url' => ['url' => $imageDataUrl]],
                            ],
                        ]],
                        'max_tokens' => 1200,
                        'temperature' => 0.05,
                    ]);
                $reviewText = trim((string) data_get($review->json(), 'choices.0.message.content', ''));
                $reviewText = preg_replace('/^```(?:json)?\s*|\s*```$/u', '', $reviewText);
                $reviewData = json_decode($reviewText, true);
                $reviewPolygon = $reviewData['floor_polygon'] ?? null;
                if ($review->successful() && is_array($reviewPolygon) && count($reviewPolygon) >= 6) {
                    $clean = collect($reviewPolygon)->map(function ($point) {
                        return [max(0, min(100, (float) ($point[0] ?? 0))), max(0, min(100, (float) ($point[1] ?? 0)))];
                    })->values()->all();
                    return ['ok' => true, 'message' => (string) ($reviewData['floor_notes'] ?? $data['floor_notes'] ?? 'سطح کف با بررسی دوم دقیق‌تر شد.'), 'floor_polygon' => $clean, 'confidence' => max(0, min(1, (float) ($reviewData['confidence'] ?? $data['confidence'] ?? 0)))];
                }
                return ['ok' => true, 'message' => (string) ($data['floor_notes'] ?? 'سطح کف برای نمایش محصول تشخیص داده شد.'), 'floor_polygon' => $clean, 'confidence' => max(0, min(1, (float) ($data['confidence'] ?? 0)))];
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return ['ok' => false, 'message' => 'تشخیص خودکار کف انجام نشد. می‌توانیم تصویر را نگه داریم و نمایش اولیه را ادامه دهیم.', 'floor_polygon' => null];
    }
    private function matchProducts(string $message, $products)
    {
        $query = $this->normalizeSearchText($message);
        if ($query === '') {
            return collect();
        }

        $tokens = collect(preg_split('/\s+/u', $query))
            ->filter(fn ($token) => mb_strlen($token) >= 2)
            ->unique()
            ->values();

        return $products
            ->map(function ($product) use ($query, $tokens) {
                $haystack = $this->normalizeSearchText(implode(' ', array_filter([
                    $product['name'] ?? '',
                    $product['model'] ?? '',
                    $product['code'] ?? '',
                    $product['tone'] ?? '',
                    $product['category'] ?? '',
                    $product['description'] ?? '',
                ])));
                $score = 0;
                if ($haystack !== '' && str_contains($haystack, $query)) {
                    $score += 20;
                }
                foreach ($tokens as $token) {
                    if (str_contains($haystack, $token)) {
                        $score += 2;
                    }
                }
                if (($product['code'] ?? '') !== '' && str_contains($query, $this->normalizeSearchText($product['code']))) {
                    $score += 30;
                }
                if (($product['model'] ?? '') !== '' && str_contains($query, $this->normalizeSearchText($product['model']))) {
                    $score += 15;
                }
                return ['product' => $product, 'score' => $score];
            })
            ->filter(fn ($item) => $item['score'] > 0)
            ->sortByDesc('score')
            ->take(8)
            ->pluck('product')
            ->values();
    }

    private function normalizeSearchText(string $text): string
    {
        $text = mb_strtolower(trim($text));
        return str_replace(['ي','ى','ك','ة','ۀ','ؤ','إ','أ','ٱ'], ['ی','ی','ک','ه','ه','و','ا','ا','ا'], $text);
    }

    private function contextText(array $context): string
    {
        $parts = [];
        if (!empty($context['surface'])) {
            $parts[] = 'نوع کف انتخاب‌شده در Visualizer: '.mb_substr((string) $context['surface'], 0, 40);
        }
        if (!empty($context['product']['name'])) {
            $parts[] = 'محصول انتخاب‌شده: '.mb_substr((string) $context['product']['name'], 0, 160);
        }
        if (!empty($context['product']['tone'])) {
            $parts[] = 'تون محصول انتخاب‌شده: '.mb_substr((string) $context['product']['tone'], 0, 80);
        }
        if (!empty($context['product']['code'])) {
            $parts[] = 'کد محصول انتخاب‌شده: '.mb_substr((string) $context['product']['code'], 0, 100);
        }
        if (!empty($context['product']['model'])) {
            $parts[] = 'مدل/آلبوم محصول انتخاب‌شده: '.mb_substr((string) $context['product']['model'], 0, 120);
        }
        if (!empty($context['compare']) && is_array($context['compare'])) {
            $compareLines = collect($context['compare'])
                ->take(2)
                ->map(fn ($item, $index) => implode(' | ', array_filter([
                    'مقایسه '.($index + 1),
                    'شناسه: '.($item['id'] ?? ''),
                    'نام: '.mb_substr((string) ($item['name'] ?? ''), 0, 160),
                    'مدل/آلبوم: '.mb_substr((string) ($item['model'] ?? ''), 0, 120),
                    'کد: '.mb_substr((string) ($item['code'] ?? ''), 0, 100),
                    'تون: '.mb_substr((string) ($item['tone'] ?? ''), 0, 80),
                ])))
                ->filter()
                ->implode("\n");
            if ($compareLines !== '') {
                $parts[] = "دو محصول فعلی Visualizer برای مقایسه:\n".$compareLines;
                $parts[] = 'در مقایسه فقط همین دو محصول را بر اساس داده کاتالوگ توضیح بده و برنده تعیین نکن.';
            }
        }
        if (!empty($context['space_analyzed'])) {
            $parts[] = 'عکس فضای کاربر قبلاً با Vision بررسی شده و محدوده کف شناسایی شده است.';
        }
        return $parts ? "\n\nزمینه فعلی کاربر:\n".implode("\n", $parts) : '';
    }

    private function isMostlyLatin(string $text): bool
    {
        $letters = preg_match_all('/[A-Za-zآ-ی]/u', $text, $matches);
        $latin = preg_match_all('/[A-Za-z]/', $text, $matches);
        return $letters > 20 && $latin / max(1, $letters) > 0.55;
    }

    private function fallback(string $message): string
    {
        $t = mb_strtolower($message);
        if (preg_match('/سلام|درود|وقت بخیر/u', $t)) {
            return 'سلام 👋 من مشاور پالاز هستم. برای چه فضایی دنبال پوشش هستید؟';
        }
        if (str_contains($t, 'اندازه')) {
            return 'اگر متراژ دقیق ندارید، می‌توانید درخواست اندازه‌گیری ثبت کنید تا ادامه مسیر را راهنمایی‌تان کنم.';
        }
        if (str_contains($t, 'نصب') || str_contains($t, 'اجرا')) {
            return 'برای نصب و اجرا می‌توانید از بخش خدمات پالاز درخواستتان را ثبت کنید.';
        }
        if (str_contains($t, 'قیمت') || str_contains($t, 'هزینه')) {
            return 'برای برآورد دقیق، نام محصول و متراژ را بگویید؛ مثلاً «لمینت برای ۶۰ متر».';
        }
        if (str_contains($t, 'موکت')) {
            return 'حتماً. برای پیشنهاد دقیق موکت، بگویید برای چه فضایی است و حدوداً چند مترمربع؟';
        }
        if (str_contains($t, 'لمینت')) {
            return 'حتماً. برای لمینت، متراژ و سبک موردنظرتان را بگویید تا گزینه مناسب را بررسی کنیم.';
        }
        return 'حتماً. بگویید برای چه فضایی، با چه متراژی و چه نوع پوششی دنبال گزینه مناسب هستید.';
    }

    private function actions(string $message): array
    {
        $t = mb_strtolower($message);
        $actions = [];
        if (str_contains($t, 'اندازه') || str_contains($t, 'نصب') || str_contains($t, 'اجرا')) {
            $actions[] = ['label' => str_contains($t, 'نصب') || str_contains($t, 'اجرا') ? 'درخواست نصب' : 'درخواست اندازه‌گیری', 'url' => route('services')];
        }
        if (str_contains($t, 'قیمت') || str_contains($t, 'هزینه') || str_contains($t, 'محاسبه')) {
            $actions[] = ['label' => 'محاسبه و برآورد', 'url' => route('shop')];
        }
        if (str_contains($t, 'محصول') || str_contains($t, 'موکت') || str_contains($t, 'لمینت') || str_contains($t, 'کاغذ دیواری')) {
            $actions[] = ['label' => 'مشاهده محصولات', 'url' => route('shop')];
        }
        return array_slice($actions, 0, 3);
    }
}
