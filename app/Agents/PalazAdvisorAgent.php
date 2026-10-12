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
            ->take(-20)->values()->all();

        // حفظ زمینه اصلی درخواست حتی وقتی گفتگو چند نوبت طول کشیده باشد.
        $routingText = collect($history)
            ->filter(fn ($item) => ($item['role'] ?? '') === 'user')
            ->pluck('content')
            ->push($message)
            ->implode(' ');
        $routingText = $this->appendImpliedAreaAnswer($routingText, $message, $history);
        $normalizedRoutingText = $this->normalizeSearchText($routingText);
        $prioritizeChildSoftnessCatalog = (bool) preg_match('/اتاق خواب کودک|اتاق کودک|اتاق بچه|اتاق فرزند/u', $normalizedRoutingText)
            && (bool) preg_match('/موکت/u', $normalizedRoutingText)
            && (bool) preg_match('/نرمی|نرم|لطافت/u', $normalizedRoutingText);
        $lastAssistantForRouting = collect($history)
            ->filter(fn ($item) => ($item['role'] ?? '') === 'assistant')
            ->last();
        $lastAssistantForRoutingText = $this->normalizeSearchText((string) ($lastAssistantForRouting['content'] ?? ''));
        $previousOfficeOptionsPresented = str_contains($lastAssistantForRoutingText, 'رنگ ظاهری تصویر')
            || str_contains($lastAssistantForRoutingText, 'نمونه‌ها از آلبوم‌های ثبت‌شده برای کاربرد اداری');
        $currentOfficePreferenceRequest = (bool) preg_match(
            '/چرک\\s*تاب|چرکتاب|تیره|رنگ|دوام|تردد بالا|پرتردد|رفت و آمد|رفت\\s*و\\s*آمد/u',
            $this->normalizeSearchText($message)
        );
        $suppressOfficeReanalysis = $previousOfficeOptionsPresented && !$currentOfficePreferenceRequest;
        $hasOfficeContext = (bool) preg_match('/محیط اداری|اداری|اداره|دفتر|محل کار/u', $normalizedRoutingText)
            && (bool) preg_match('/موکت/u', $normalizedRoutingText);
        $hasOfficePreference = (bool) preg_match(
            '/دوام|تردد بالا|پرتردد|رفت و آمد|رفت\\s*و\\s*آمد|چرک\\s*تاب|چرکتاب|تیره|رنگ/u',
            $normalizedRoutingText
        );
        $prioritizeOfficeTrafficCatalog = $hasOfficeContext && $hasOfficePreference && !$suppressOfficeReanalysis;

        // وقتی درخواست نرمی موکت کودک یا یک فیلتر اداری مشخص کامل است، باید مستقیماً به کاتالوگ برسیم؛
        // در پیام‌های پیگیری قیمت/کد، نتیجه رنگ را دوباره تحلیل نکن.
        $guidedReply = $this->guidedConversationReply($message, $history);
        if ($guidedReply !== null && !$prioritizeChildSoftnessCatalog && !$prioritizeOfficeTrafficCatalog) {
            return ['reply' => $guidedReply, 'mode' => 'guided', 'actions' => []];
        }

        // اگر کاتالوگ/دیتابیس موقتاً در دسترس نبود، گفتگو نباید با خطای 500 قطع شود.
        // مشاور می‌تواند بدون کاتالوگ هم سؤال عمومی را پاسخ دهد و برای قیمت، کاربر را به فروشگاه هدایت کند.
        try {
            $allProducts = collect(StoreCatalog::products());
        } catch (\Throwable $e) {
            report($e);
            $allProducts = collect();
        }

        // فهرست کامل را به مدل نمی‌دهیم؛ فقط گزینه‌هایی که موتور تطبیق پیدا کرده اجازه ورود به پاسخ دارند.
        $catalog = '';

        // قبل از ارسال به مدل، محصولات مرتبط با متن کاربر را از کاتالوگ واقعی پیدا می‌کنیم
        // تا مشاور مجبور نباشد بین کل کاتالوگ حدس بزند.
        $conversationText = collect($history)
            ->filter(fn ($item) => ($item['role'] ?? '') === 'user')
            ->pluck('content')
            ->push($message)
            ->implode(' ');
        $conversationText = $this->appendImpliedAreaAnswer($conversationText, $message, $history);
        $advisorProducts = $this->filterProductsForAdvisorNeed($conversationText, $allProducts);
        $matchedProducts = $this->matchProducts($conversationText, $advisorProducts);
        $normalizedConversation = $this->normalizeSearchText($conversationText);
        $hasChildSoftnessRequest = (bool) preg_match('/اتاق خواب کودک|اتاق کودک|اتاق بچه|اتاق فرزند/u', $normalizedConversation)
            && (bool) preg_match('/نرمی|نرم|لطافت/u', $normalizedConversation)
            && (bool) preg_match('/موکت/u', $normalizedConversation);

        // پیشنهاد نرمی برای اتاق کودک باید از ترتیب ارتفاع پرز ثبت‌شده استفاده کند،
        // نه از تصادفِ ترتیب کاتالوگ یا رتبه‌بندی براساس قیمت و عکس.
        if ($hasChildSoftnessRequest) {
            $matchedProducts = $this->childRoomSoftnessProducts($advisorProducts);
            if ($matchedProducts->isEmpty()) {
                return [
                    'reply' => 'متوجه شدم؛ برای نرمی موکت اتاق کودک، اما مدل‌های دارای ارتفاع پرزِ ثبت‌شده را در کاتالوگ فعال پیدا نکردم. نمی‌خواهم گزینه نامطمئن معرفی کنم. می‌توانیم نام آلبوم یا کدهای موجود را با هم بررسی کنیم.',
                    'mode' => 'catalog',
                    'actions' => [],
                    'products' => [],
                ];
            }

            $lines = $matchedProducts->map(function ($product) {
                $album = (string) ($product['model'] ?? '');
                $height = $this->pileHeightForChildSoftness($album);
                $fiber = $this->fiberTypeForAlbum($album) ?? 'جنس نخ در داده فعلی تأیید نشده';
                $price = ($product['price'] ?? null) !== null
                    ? number_format((float) $product['price']).' تومان به ازای '.($product['unit'] ?? 'واحد فروش')
                    : 'قیمت استعلامی';
                return '• آلبوم '.$album
                    .(!empty($product['code']) ? '، کد '.$product['code'] : '')
                    .'؛ جنس نخ ثبت‌شده: '.$fiber
                    .($height !== null ? '؛ ارتفاع پرز ثبت‌شده: '.$height.' سانتی‌متر' : '')
                    .'؛ '.$price;
            })->implode(PHP_EOL);

            return [
                'reply' => 'برای اتاق خواب کودک، چون نرمی برایتان مهم است، این مدل‌های موکت را برای بررسی مقایسه‌ای پیشنهاد می‌کنم. ترتیب زیر بر اساس ارتفاع پرزِ ثبت‌شده است؛ ارتفاع پرز به‌تنهایی تضمین‌کننده حس نرمی نهایی نیست و بهتر است نمونه را هم لمس کنید:'.PHP_EOL.$lines,
                'mode' => 'catalog',
                'actions' => [],
                'products' => $this->productCards($matchedProducts, $conversationText),
            ];
        }

        $hasOfficeTrafficRequest = $hasOfficeContext
            && $hasOfficePreference
            && !$suppressOfficeReanalysis;

        if ($hasOfficeTrafficRequest) {
            $officeCandidates = $this->officeTrafficProducts($advisorProducts);
            if ($officeCandidates->isEmpty()) {
                return [
                    'reply' => 'برای این درخواست، آلبوم‌های موکت اداریِ ثبت‌شده را در کاتالوگ فعال پیدا نکردم؛ بنابراین موکت خانگی را به‌جای آن پیشنهاد نمی‌دهم. اگر کد یا نام آلبوم اداری خاصی دارید، بفرمایید تا همان را بررسی کنم.',
                    'mode' => 'catalog',
                    'actions' => [],
                    'products' => [],
                ];
            }

            $hasOfficeDarkColorRequest = (bool) preg_match(
                '/چرک\\s*تاب|چرکتاب|تیره|رنگ/u',
                $this->normalizeSearchText($message)
            ) || (!$previousOfficeOptionsPresented && (bool) preg_match(
                '/چرک\\s*تاب|چرکتاب|تیره|رنگ/u',
                $normalizedConversation
            ));

            if ($hasOfficeDarkColorRequest) {
                $visionKey = (string) config('services.openrouter.key');
                $matchedProducts = $visionKey !== ''
                    ? $this->rankOfficeColorVariants($officeCandidates, $visionKey)
                    : collect();

                if ($matchedProducts->isEmpty()) {
                    return [
                        'reply' => 'مدل و کدهای اداری را در کاتالوگ پیدا کردم، اما رنگِ عکس کدها را فعلاً با اطمینان نتوانستم تحلیل کنم. برای اینکه کد اشتباه ندهم، رنگ تیره یا چرک‌تاب را حدس نمی‌زنم؛ باید عکس دقیق کدها و سرویس تحلیل تصویر در دسترس باشد.',
                        'mode' => 'catalog',
                        'actions' => [],
                        'products' => [],
                    ];
                }
            } else {
                $matchedProducts = $this->officeTrafficRepresentativeProducts($officeCandidates);
            }

            $area = $this->extractAdvisorArea($conversationText);
            $areaText = $area !== null
                ? ' با درنظرگرفتن متراژ اعلام‌شده شما ('.rtrim(rtrim(number_format($area, 2, '.', ''), '0'), '.').' مترمربع)'
                : '';
            $lines = $matchedProducts->map(function ($product) {
                $album = (string) ($product['model'] ?? '');
                $fiber = $this->fiberTypeForAlbum($album) ?? 'جنس نخ در داده فعلی تأیید نشده';
                $pile = $this->pileTypeForAlbum($album) ?? 'نوع بافت در داده فعلی تأیید نشده';
                $price = ($product['price'] ?? null) !== null
                    ? number_format((float) $product['price']).' تومان به ازای '.($product['unit'] ?? 'واحد فروش')
                    : 'قیمت استعلامی';
                $visualTone = trim((string) ($product['_visual_tone_fa'] ?? ''));
                return '• آلبوم '.$album
                    .(!empty($product['code']) ? '، کد '.$product['code'] : '')
                    .($visualTone !== '' ? '؛ رنگ ظاهری تصویر: '.$visualTone : '')
                    .'؛ جنس نخ ثبت‌شده: '.$fiber
                    .'؛ بافت ثبت‌شده: '.$pile
                    .'؛ '.$price;
            })->implode(PHP_EOL);

            $hasHighTrafficNeed = (bool) preg_match('/دوام|تردد بالا|پرتردد|رفت و آمد|رفت\\s*و\\s*آمد/u', $normalizedConversation);
            $officeNeedLabel = $hasOfficeDarkColorRequest
                ? ($hasHighTrafficNeed ? 'موکت اداری پرتردد با رنگ تیره و چرک‌تاب' : 'موکت اداری با رنگ تیره و چرک‌تاب')
                : 'موکت اداری برای بررسی کاربرد پرتردد';

            return [
                'reply' => 'برای '.$officeNeedLabel.$areaText.'، این گزینه‌ها از آلبوم‌های ثبت‌شده برای کاربرد اداری در کاتالوگ فعال پیدا شدند:'.PHP_EOL.$lines.PHP_EOL
                    .($hasOfficeDarkColorRequest ? 'رنگ‌ها از روی عکس کدهای قابل‌دسترس برآورد شده‌اند و ممکن است با نور محیط یا نمایشگر کمی تفاوت داشته باشند. ' : '')
                    .'طبق اطلاعات فعلی، آلبوم‌های اداری نام‌برده‌شده بافت لوپ پایل دارند؛ اما داده مقایسه‌ای کافی برای رتبه‌بندی دوام این مدل‌ها در برابر یکدیگر ندارم، پس آن‌ها را به‌عنوان گزینه‌های قابل‌بررسی معرفی می‌کنم، نه رتبه‌بندی قطعی. برای انتخاب نهایی می‌توانیم کد، رنگ و قیمت هر مدل را بررسی کنیم.',
                'mode' => 'catalog',
                'actions' => [],
                'products' => $this->productCards($matchedProducts, $conversationText),
            ];
        }

        $hasSpaceForRecommendation = preg_match('/خانه|خانگی|منزل|پذیرایی|اتاق|راهرو|آشپزخانه|محل کار|اداری|اداره|دفتر|هتل|لابی|نمازخانه/u', $this->normalizeSearchText($conversationText));
        $hasAreaForRecommendation = $this->extractAdvisorArea($conversationText) !== null;
        $hasSpecificCovering = preg_match('/موکت|لمینت|پارکت|اس\\s*پی\\s*سی|spc|فرش[\\s‌-]*گونه|چمن|کاغذ\\s*دیواری|گارد|اسپاگتی/u', $this->normalizeSearchText($conversationText));

        // وقتی نیاز اصلی کامل است اما در دیتابیس گزینه مرتبط وجود ندارد،
        // پیش از فراخوانی مدل جلوی ساختن نام یا مشخصات خیالی را می‌گیریم.
        if ($hasSpaceForRecommendation && $hasAreaForRecommendation && $hasSpecificCovering && $matchedProducts->isEmpty()) {
            return [
                'reply' => 'نیازتان را متوجه شدم، اما در کاتالوگ فعلی محصول مرتبطی برای تأیید پیدا نکردم. نمی‌خواهم مدل، کد یا قیمت حدسی به شما بدهم. اگر بخواهید، می‌توانیم دسته‌بندی محصولات را بررسی کنیم یا با نام آلبوم/کد دقیق‌تر جست‌وجو کنیم.',
                'mode' => 'catalog',
                'actions' => $this->actions($message),
                'products' => [],
            ];
        }

        $catalog = $matchedProducts
            ->map(fn ($p) => implode(' | ', array_filter([
                'شناسه: '.($p['id'] ?? ''),
                'نام: '.($p['name'] ?? ''),
                'دسته: '.($p['category'] ?? ''),
                'مدل/آلبوم: '.($p['model'] ?? ''),
                'جنس نخ ثبت‌شده برای آلبوم: '.($this->fiberTypeForAlbum((string) ($p['model'] ?? '')) ?? 'در کاتالوگ تأیید نشده'),
                'نوع بافت ثبت‌شده برای آلبوم: '.($this->pileTypeForAlbum((string) ($p['model'] ?? '')) ?? 'در کاتالوگ تأیید نشده'),
                'کد: '.($p['code'] ?? ''),
                'قیمت: '.(($p['price'] ?? null) !== null ? number_format((float) $p['price']).' تومان' : 'استعلامی'),
                'واحد: '.($p['unit'] ?? ''),
                'رنگ/تون: '.($p['tone'] ?? ''),
                'توضیح: '.($p['description'] ?? ''),
            ])))
            ->take(8)
            ->implode("\n");
        $area = $this->extractAdvisorArea($conversationText);
        $matchedText = $matchedProducts->isNotEmpty()
            ? "\n\nمحصولات مرتبط با پیام فعلی کاربر از کاتالوگ واقعی:\n"
                .$matchedProducts->map(fn ($p) => implode(' | ', array_filter([
                    'شناسه: '.($p['id'] ?? ''),
                    'نام: '.($p['name'] ?? ''),
                    'دسته: '.($p['category'] ?? ''),
                    'مدل/آلبوم: '.($p['model'] ?? ''),
                    'جنس نخ آلبوم: '.($this->fiberTypeForAlbum((string) ($p['model'] ?? '')) ?? 'در کاتالوگ تأیید نشده'),
                    'نوع بافت آلبوم: '.($this->pileTypeForAlbum((string) ($p['model'] ?? '')) ?? 'در کاتالوگ تأیید نشده'),
                    'کد: '.($p['code'] ?? ''),
                    'قیمت ثبت‌شده: '.($p['price'] !== null ? number_format((float)$p['price']).' تومان' : 'استعلامی'),
                    'واحد: '.($p['unit'] ?? ''),
                    'رنگ/تون: '.($p['tone'] ?? ''),
                ])))->implode("\n")
            : '';

        $calculationText = $area !== null
            ? $this->calculationContext($matchedProducts, $area)
            : '';

        $apiKey = (string) config('services.openrouter.key');

        if ($apiKey !== '') {
            $system = <<<'PROMPT'
تو «مشاور هوشمند پالاز» هستی؛ یک مشاور فارسی‌زبان برای انتخاب، مقایسه و برنامه‌ریزی خرید و اجرای محصولات پالاز.
لحن: صمیمی، حرفه‌ای، کوتاه و طبیعی؛ فروشنده اصراری نباش.
اول نیاز مشتری را بفهم: فضا، متراژ، سبک/اولویت، بودجه و محدودیت‌ها. تاریخچه گفتگو را کامل بخوان و اطلاعاتی را که مشتری قبلاً گفته دوباره نپرس. در هر نوبت حداکثر دو سؤال ضروری بپرس.
وقتی فضا، متراژ و نوع پوشش مشخص است، دیگر پاسخ کلی مثل «بررسی می‌کنم» نده. ابتدا محصولات بخش «گزینه‌های مرتبطِ فیلترشده» را بررسی کن و فقط اگر واقعاً مرتبط‌اند، حداکثر ۳ گزینه از همان فهرست پیشنهاد بده. هر گزینه باید نام، مدل/آلبوم، کد، قیمت ثبت‌شده یا عبارت «استعلامی» و دلیل تناسب داشته باشد. اگر فهرست مرتبط خالی است یا محصول کافی وجود ندارد، هرگز از محصولات نامرتبط یا اولین ردیف‌های کاتالوگ جایگزین نساز؛ شفاف بگو در داده فعلی گزینه قابل‌تأیید پیدا نشد و فقط یک سؤال هدفمند برای محدود کردن انتخاب بپرس.
فقط درباره محصول و قیمت از کاتالوگ داده‌شده استفاده کن و هرگز قیمت، موجودی یا مشخصات را حدس نزن. برای آلبوم‌های موکت، جنس نخ ثبت‌شده در اطلاعات مشاور چنین است: کات سی، کات بی، زیبا، افرا، نیکاس، پامچال و باران = پلی‌پروپیلن؛ یاسمن، یاس و الماس = پلی‌آمید. این نگاشت فقط برای همین آلبوم‌ها معتبر است؛ اگر نام آلبوم دقیقاً در این فهرست نیست، جنس نخ را «تأیید نشده» اعلام کن. از جنس نخ به‌تنهایی نتیجه قطعی درباره دوام یا برتری محصول نگیر. نوع بافت آلبوم‌های موکت نیز چنین است: پامچال = لوپ پایل؛ کات سی، کات بی، زیبا، افرا، نیکاس، باران، یاسمن، یاس و الماس = کات پایل. آلبوم‌های اداری/تردد بالا: پونه، نعنا، نیلوفر و شبنم = پلی‌پروپیلن؛ آسا، پتینه و راش = پلی‌آمید. همه موکت‌های اداری لوپ پایل هستند. مدل‌های هتلی VIP ونوس و دالیا: نوع نخ پلی‌آمید و بافت کات پایل فشرده است. برای نرمی موکت اتاق کودک، طبق اطلاعات ثبت‌شده کاربر، ترتیب مدل‌های پلی‌آمید چنین است: الماس با ارتفاع پرز ۲٫۵ سانتی‌متر، سپس یاس با ارتفاع پرز ۱٫۵ سانتی‌متر. در میان مدل‌های پلی‌پروپیلن: نیکاس با ارتفاع پرز ۲ سانتی‌متر، سپس افرا با ارتفاع پرز ۱٫۲ سانتی‌متر و کات بی با ارتفاع پرز ۱ سانتی‌متر. آیلین تنها مدل طرح کودک چاپیِ ثبت‌شده در این اطلاعات است؛ جنس آن پلی‌آمید و بافت آن کات پایل فشرده است و نباید آن را به‌عنوان موکت نرم پیشنهاد کنی. طرح کودک بودن به‌تنهایی به معنی نرمی نیست. برای پیشنهاد گزینه‌های نرم‌تر اتاق کودک، ترتیب ارتفاع پرزهای مدل‌های بالا را در نظر بگیر و ارتفاع پرزهای ذکرشده را مشخصه ثبت‌شده همان آلبوم‌ها بدان. برای آلبوم‌های اداریِ نام‌برده‌شده همین جنس نخ را اعلام کن؛ اگر آلبوم دیگری است، جنس نخ را تأییدنشده بگو و حدس نزن. برای نوع بافت، قاعده عمومی موکت اداری (لوپ پایل) را فقط وقتی دسته یا کاربرد اداری/تردد بالا مشخص است اعمال کن؛ در غیر این صورت برای آلبوم‌های نام‌برده‌نشده حدس نزن. اطلاعات قبلی مشتری را از تاریخچه گفتگو استخراج کن؛ پیام فعلی ممکن است فقط پاسخ یک سؤال باشد.
مهم: زبان خروجی همیشه فارسی باشد. حتی اگر پیام کاربر با صدا تشخیص داده شده، پاسخ را فارسی و با خط فارسی تولید کن؛ از انگلیسی استفاده نکن مگر برای نام خاص محصول، برند یا کد.
اگر اطلاعات کافی است، 2 تا 3 گزینه را با دلیل کوتاه مقایسه کن.
اگر کاربر در Visualizer دو محصول را مقایسه می‌کند، همان دو محصول را مبنای پاسخ قرار بده و تفاوت‌ها را فقط بر اساس اطلاعات کاتالوگ توضیح بده؛ محصولی را به‌عنوان «بهترین» یا «برنده» اعلام نکن.
اگر کاربر درباره اندازه‌گیری، نصب، طراحی یا محاسبه پرسید، مسیر خدمات پالاز را پیشنهاد کن.
همیشه فارسی پاسخ بده مگر نام محصول/برند/کد ذاتاً لاتین باشد.
اگر سؤال خارج از حوزه پالاز است ولی پاسخ عمومی و قابل‌اعتمادش را می‌دانی، پاسخ مفید و کوتاه بده و اگر برای آن حوزه تخصصی نیستی این محدودیت را شفاف بگو؛ گفتگو را بی‌دلیل قطع نکن. برای موضوعات حساس یا نیازمند اطلاعات به‌روز، ادعای قطعی نکن و کاربر را به منبع مناسب راهنمایی کن.
کاتالوگ فعلی:
PROMPT;
            $contextText = $this->contextText($context).$matchedText.$calculationText;
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

                    return ['reply' => $text, 'mode' => 'ai', 'actions' => $this->actions($message), 'products' => $this->productCards($matchedProducts, $conversationText)];
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return ['reply' => $this->fallback($message, $history, $allProducts), 'mode' => 'catalog', 'actions' => $this->actions($message), 'products' => $this->productCards($matchedProducts, $conversationText)];
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
    private function extractAdvisorArea(string $message): ?float
    {
        $normalized = strtr($message, [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
            '٫'=>'.', '٬'=>',',
        ]);

        // فقط متراژ را از عبارت‌هایی مثل «۶۰ متر»، «۶۰ مترمربع» یا «متراژ ۶۰» استخراج می‌کنیم.
        if (preg_match('/(?:متراژ|مساحت|زیربنا)\\s*[:：]?\\s*(\\d+(?:[.,]\\d+)?)|(?:\\b)(\\d+(?:[.,]\\d+)?)\\s*(?:متر\\s*مربع|مترمربع|متر)(?:\\b)/u', $normalized, $matches)) {
            $value = $matches[1] !== '' ? $matches[1] : ($matches[2] ?? '');
            $value = str_replace(',', '.', $value);
            $area = (float) $value;
            return $area > 0 && $area <= 100000 ? $area : null;
        }

        return null;
    }

    /**
     * Interpret a bare number as an area only when the immediately preceding advisor turn asked for area.
     * This avoids treating standalone product codes or other numbers as square metres.
     */
    private function areaForCurrentTurn(string $message, array $history): ?float
    {
        $normalized = $this->normalizeSearchText($message);
        if (!preg_match('/^\\s*(\\d+(?:[.,]\\d+)?)\\s*$/u', $normalized, $matches)) {
            return null;
        }

        $previousAssistant = collect($history)
            ->filter(fn ($item) => ($item['role'] ?? '') === 'assistant')
            ->last();
        $question = $this->normalizeSearchText((string) ($previousAssistant['content'] ?? ''));
        if (!preg_match('/متراژ|مساحت|زیربنا|مترمربع|متر مربع|چند متر|چه متراژی/u', $question)) {
            return null;
        }

        $area = (float) str_replace(',', '.', $matches[1]);
        return $area > 0 && $area <= 100000 ? $area : null;
    }

    private function appendImpliedAreaAnswer(string $conversationText, string $message, array $history): string
    {
        if ($this->extractAdvisorArea($conversationText) !== null) {
            return $conversationText;
        }

        // First prefer a numeric reply from the current turn, if the last advisor question asked for area.
        $currentArea = $this->areaForCurrentTurn($message, $history);
        if ($currentArea !== null) {
            $areaLabel = rtrim(rtrim(number_format($currentArea, 2, '.', ''), '0'), '.');
            return $conversationText.' متراژ '.$areaLabel.' مترمربع';
        }

        // Remember earlier bare-number replies (for example "15") by checking what the advisor asked immediately before them.
        // Without this, the area disappears from context as soon as the customer answers "موکت" or "نرمی".
        $lastAssistantText = '';
        $rememberedArea = null;
        foreach ($history as $entry) {
            $role = $entry['role'] ?? '';
            $text = trim((string) ($entry['content'] ?? ''));

            if ($role === 'assistant') {
                $lastAssistantText = $this->normalizeSearchText($text);
                continue;
            }

            if ($role !== 'user') {
                continue;
            }

            $normalized = $this->normalizeSearchText($text);
            if (!preg_match('/^\\s*(\\d+(?:[.,]\\d+)?)\\s*$/u', $normalized, $matches)) {
                continue;
            }

            if (!preg_match('/متراژ|مساحت|زیربنا|مترمربع|متر مربع|چند متر|چه متراژی/u', $lastAssistantText)) {
                continue;
            }

            $area = (float) str_replace(',', '.', $matches[1]);
            if ($area > 0 && $area <= 100000) {
                $rememberedArea = $area;
            }
        }

        if ($rememberedArea === null) {
            return $conversationText;
        }

        $areaLabel = rtrim(rtrim(number_format($rememberedArea, 2, '.', ''), '0'), '.');
        return $conversationText.' متراژ '.$areaLabel.' مترمربع';
    }

    private function guidedConversationReply(string $message, array $history): ?string
    {
        $normalize = fn (string $text) => $this->normalizeSearchText($text);
        $current = $normalize($message);
        $previousAssistant = collect($history)
            ->filter(fn ($item) => ($item['role'] ?? '') === 'assistant')
            ->last();
        $assistantText = $normalize((string) ($previousAssistant['content'] ?? ''));
        $conversationText = collect($history)
            ->filter(fn ($item) => ($item['role'] ?? '') === 'user')
            ->pluck('content')
            ->push($message)
            ->implode(' ');
        $conversationText = $this->appendImpliedAreaAnswer($conversationText, $message, $history);
        $all = $normalize($conversationText);

        // مشتری ممکن است در هر مرحله دوباره سلام کند؛ همیشه سلام را پاسخ بده
        // و پس از آن متناسب با اطلاعات قبلی فقط سؤال بعدیِ لازم را مطرح کن.
        if (preg_match('/^(?:سلام|درود|وقت\\s*بخیر|صبح\\s*بخیر|عصر\\s*بخیر|شب\\s*بخیر)[.!؟،,\\s]*$/u', $current)) {
            $greeting = 'سلام، وقت شما بخیر 🌷 خوش آمدید. ';
            $hasSpaceBeforeGreeting = (bool) preg_match('/خانه|خانگی|منزل|پذیرایی|اتاق|راهرو|آشپزخانه|محل کار|اداری|اداره|دفتر|هتل|لابی|نمازخانه/u', $all);
            $hasAreaBeforeGreeting = $this->extractAdvisorArea($conversationText) !== null;
            $hasCoveringBeforeGreeting = (bool) preg_match('/موکت|لمینت|اس\\s*پی\\s*سی|spc|پارکت|چمن|کاغذ\\s*دیواری|گارد|اسپاگتی/u', $all);
            $hasHomePartBeforeGreeting = (bool) preg_match('/پذیرایی|اتاق خواب|اتاق کودک|اتاق|راهرو|آشپزخانه|بالکن/u', $all);
            $hasHotelPartBeforeGreeting = (bool) preg_match('/اتاق|راهرو|لابی|سالن|پذیرش|رستوران/u', $all);

            if (!$hasSpaceBeforeGreeting) {
                return $greeting.'برای چه فضایی و چه نوع پوششی دنبال گزینه مناسب هستید؟';
            }
            if (preg_match('/خانه|خانگی|منزل/u', $all) && !$hasHomePartBeforeGreeting) {
                return $greeting.'برای کدام قسمت خانه است؛ پذیرایی، اتاق خواب، راهرو یا آشپزخانه؟';
            }
            if (preg_match('/هتل/u', $all) && !$hasHotelPartBeforeGreeting) {
                return $greeting.'برای کدام بخش هتل است؛ اتاق، راهرو، لابی یا سالن؟';
            }
            if (!$hasAreaBeforeGreeting) {
                return $greeting.'حدوداً چند مترمربع است؟ متراژ تقریبی هم کافی است.';
            }
            if (!$hasCoveringBeforeGreeting) {
                return $greeting.'برای این فضا چه نوع پوششی مدنظر دارید؛ موکت، لمینت، SPC یا گزینه دیگری؟';
            }

            return $greeting.'در خدمتم. اولویت اصلی‌تان چیست: قیمت اقتصادی، دوام، نرمی و راحتی یا طرح و رنگ؟';
        }

        // سؤال بودجه نباید باعث شود اطلاعاتی را که مشتری همین حالا گفته دوباره نپرسیم.
        if (preg_match('/بودجه|هزینه|مبلغ/u', $assistantText)) {
            $missing = [];
            $hasSpace = preg_match('/خانه|خانگی|منزل|پذیرایی|اتاق|راهرو|آشپزخانه|محل کار|اداری|اداره|دفتر|هتل|لابی|نمازخانه/u', $all);
            $hasArea = $this->extractAdvisorArea($conversationText) !== null;
            $hasCovering = preg_match('/موکت|لمینت|اس\\s*پی\\s*سی|spc|پارکت|چمن|کاغذ\\s*دیواری|گارد|اسپاگتی/u', $all);

            if (!$hasSpace) $missing[] = 'فضای موردنظر';
            if (!$hasArea) $missing[] = 'متراژ تقریبی';
            if (!$hasCovering) $missing[] = 'نوع پوشش';
            return $missing
                ? 'ممنون. برای پیشنهاد دقیق، فقط این موردها هنوز مشخص نشده‌اند: '.implode('، ', $missing).'.'
                : null;
        }

        // اشاره مستقیم به اتاق یا بخش‌های خانه را هم به‌عنوان فضای خانگی تشخیص بده.
        // مشتری ممکن است پاسخ کوتاه بدهد: «اتاق خواب کودک» بدون تکرار کلمه «خانه».
        $isHome = preg_match('/خانه|خانگی|منزل|پذیرایی|اتاق خواب|اتاق کودک|اتاق بچه|اتاق فرزند|اتاق|راهرو|آشپزخانه|بالکن/u', $all);
        $isOffice = preg_match('/محل کار|اداری|اداره|دفتر|دفتر کار/u', $all);
        $isHotel = preg_match('/هتل/u', $all);
        $isPrayer = preg_match('/نمازخانه/u', $all);
        $hasSpace = (bool) preg_match('/خانه|خانگی|منزل|پذیرایی|اتاق|راهرو|آشپزخانه|محل کار|اداری|اداره|دفتر|هتل|لابی|نمازخانه/u', $all);
        $hasArea = $this->extractAdvisorArea($conversationText) !== null;
        $hasCovering = preg_match('/موکت|لمینت|اس\\s*پی\\s*سی|spc|پارکت|چمن|کاغذ\\s*دیواری|گارد|اسپاگتی/u', $all);
        $hasHomePart = preg_match('/پذیرایی|اتاق خواب|اتاق کودک|اتاق|راهرو|آشپزخانه|بالکن/u', $all);
        $hasHotelPart = preg_match('/اتاق|راهرو|لابی|سالن|پذیرش|رستوران/u', $all);
        $wasAskingSpace = str_contains($assistantText, 'برای چه فضایی')
            || str_contains($assistantText, 'چه فضایی')
            || (str_contains($assistantText, 'فضا') && str_contains($assistantText, 'نوع پوشش'))
            // حتی اگر مشتری بدون سلام و بدون انتظار سؤال قبلی مستقیماً نام فضا را بگوید، مسیر راهنما را ادامه بده.
            || $hasSpace;
        $wasAskingArea = (bool) preg_match('/متراژ|مساحت|زیربنا|مترمربع|متر مربع|چند متر|چه متراژی/u', $assistantText);
        $bareAreaAnswer = $this->areaForCurrentTurn($message, $history);

        // پاسخ عددی کوتاه را فقط وقتی متراژ بدان که مشاور نوبت قبل درباره مساحت پرسیده باشد.
        // سپس فقط اطلاعات واقعاً نامشخص را سؤال می‌کنیم؛ نه اینکه پرسش آغازین را تکرار کنیم.
        if ($bareAreaAnswer !== null && $wasAskingArea) {
            $areaLabel = rtrim(rtrim(number_format($bareAreaAnswer, 2, '.', ''), '0'), '.').' مترمربع';
            if (!$hasSpace) {
                return !$hasCovering
                    ? 'متراژ '.$areaLabel.' را ثبت کردم. برای چه فضایی است و چه نوع پوششی مدنظر دارید؟'
                    : 'متراژ '.$areaLabel.' را ثبت کردم. این متراژ برای چه فضایی است؟';
            }
            if ($isHome && !$hasHomePart) {
                return !$hasCovering
                    ? 'متراژ '.$areaLabel.' را ثبت کردم. کدام قسمت خانه است و چه نوع پوششی مدنظر دارید؟'
                    : 'متراژ '.$areaLabel.' را ثبت کردم. برای کدام قسمت خانه است؛ پذیرایی، اتاق خواب، راهرو یا آشپزخانه؟';
            }
            if ($isHotel && !$hasHotelPart) {
                return !$hasCovering
                    ? 'متراژ '.$areaLabel.' را ثبت کردم. کدام بخش هتل است و چه نوع پوششی مدنظر دارید؟'
                    : 'متراژ '.$areaLabel.' را ثبت کردم. برای کدام بخش هتل است؛ اتاق، راهرو، لابی یا سالن؟';
            }
            if (!$hasCovering) {
                return 'متراژ '.$areaLabel.' را ثبت کردم. برای این فضا چه نوع پوششی مدنظر دارید؛ موکت، لمینت، SPC یا گزینه دیگری؟';
            }
            $hasPreference = preg_match('/اقتصادی|ارزان|گران|بودجه|قیمت|دوام|رفت\\s*و\\s*آمد|پرتردد|نرمی|راحتی|شست\\s*و\\s*شو|ضدلک|ضد\\s*لک|رنگ|طرح|لوکس|ساده/u', $all);
            if (!$hasPreference) {
                return 'متراژ '.$areaLabel.' را ثبت کردم. اولویت اصلی‌تان چیست: قیمت اقتصادی، دوام، نرمی و راحتی یا طرح و رنگ؟';
            }
        }

        if ($wasAskingSpace && ($isHome || $isOffice || $isHotel || $isPrayer)) {
            if ($isHome && !$hasHomePart) {
                return 'برای خانه، کدام قسمت را در نظر دارید؟ مثلاً پذیرایی، اتاق خواب، راهرو یا آشپزخانه؟';
            }
            if ($isHotel && !$hasHotelPart) {
                return 'برای هتل، کدام بخش است؟ اتاق، راهرو، لابی یا سالن؟';
            }
            if (!$hasArea) {
                if (preg_match('/اتاق خواب کودک|اتاق کودک|اتاق بچه|اتاق فرزند/u', $all)) {
                    return 'متوجه شدم، برای اتاق خواب کودک حدوداً چند مترمربع است؟ متراژ تقریبی هم کافی است.';
                }
                if ($isHome && $hasHomePart) {
                    return 'متوجه شدم. برای این قسمت خانه حدوداً چند مترمربع است؟ متراژ تقریبی هم کافی است.';
                }
                return 'حدوداً چند مترمربع است؟ اگر دقیق نمی‌دانید، متراژ تقریبی هم کافی است.';
            }
            if (!$hasCovering) {
                return 'چه نوع پوششی مدنظر دارید؛ موکت، لمینت، SPC یا گزینه دیگری؟';
            }
            $hasPreference = preg_match('/اقتصادی|ارزان|گران|بودجه|قیمت|دوام|رفت\\s*و\\s*آمد|پرتردد|نرمی|راحتی|شست\\s*و\\s*شو|ضدلک|ضد\\s*لک|رنگ|طرح|لوکس|ساده/u', $all);
            if (!$hasPreference) {
                return 'برای اینکه پیشنهادها واقعاً به کارتان بیاید، اولویت اصلی‌تان کدام است: قیمت اقتصادی، دوام در رفت‌وآمد زیاد، نرمی و راحتی، یا طرح و رنگ؟';
            }
            return null;
        }

        $wasAskingPart = str_contains($assistantText, 'کدام قسمت') || str_contains($assistantText, 'کدام بخش');
        if ($wasAskingPart) {
            if (!$hasArea) {
                return 'ممنون. حدوداً چند مترمربع است؟';
            }
            if (!$hasCovering) {
                return 'برای این فضا چه نوع پوششی مدنظر دارید؟';
            }
            $hasPreference = preg_match('/اقتصادی|ارزان|گران|بودجه|قیمت|دوام|رفت\\s*و\\s*آمد|پرتردد|نرمی|راحتی|شست\\s*و\\s*شو|ضدلک|ضد\\s*لک|رنگ|طرح|لوکس|ساده/u', $all);
            if (!$hasPreference) {
                return 'برای اینکه پیشنهادها واقعاً به کارتان بیاید، اولویت اصلی‌تان کدام است: قیمت اقتصادی، دوام در رفت‌وآمد زیاد، نرمی و راحتی، یا طرح و رنگ؟';
            }
        }

        return null;
    }

    private function childRoomSoftnessProducts($products)
    {
        $order = ['الماس', 'نیکاس', 'یاس', 'افرا', 'کات بی'];
        $rank = array_flip(array_map(fn ($name) => $this->normalizeSearchText($name), $order));

        return $products
            ->filter(function ($product) use ($rank) {
                $model = $this->normalizeSearchText((string) ($product['model'] ?? ''));
                return $model !== ''
                    && isset($rank[$model])
                    && $this->pileHeightForChildSoftness($model) !== null;
            })
            ->sortBy(fn ($product) => $rank[$this->normalizeSearchText((string) ($product['model'] ?? ''))])
            ->unique(fn ($product) => $this->normalizeSearchText((string) ($product['model'] ?? '')))
            ->take(3)
            ->values();
    }

    /**
     * Return only catalog items from the explicitly registered office/high-traffic carpet albums.
     * Order is for stable presentation only, not a durability ranking.
     */
    private function officeTrafficProducts($products)
    {
        $order = ['پونه', 'آسا', 'نعنا', 'پتینه', 'نیلوفر', 'راش', 'شبنم'];
        $rank = array_flip(array_map(fn ($name) => $this->normalizeSearchText($name), $order));

        return $products
            ->filter(function ($product) use ($rank) {
                $model = $this->normalizeSearchText((string) ($product['model'] ?? ''));
                $code = trim((string) ($product['code'] ?? ''));
                return $model !== ''
                    && $code !== ''
                    && isset($rank[$model])
                    && $this->pileTypeForAlbum($model) === 'لوپ پایل';
            })
            ->sortBy(function ($product) use ($rank) {
                $model = $this->normalizeSearchText((string) ($product['model'] ?? ''));
                return sprintf('%02d-%s', $rank[$model] ?? 99, trim((string) ($product['code'] ?? '')));
            })
            ->unique(fn ($product) => trim((string) ($product['code'] ?? '')))
            ->values();
    }

    /**
     * For a general office/high-traffic request without a color preference, show distinct
     * registered office albums and do not pretend that the selection is color-ranked.
     */
    private function officeTrafficRepresentativeProducts($products)
    {
        return $products
            ->unique(fn ($product) => $this->normalizeSearchText((string) ($product['model'] ?? '')))
            ->take(3)
            ->values();
    }

    /**
     * Compare actual, exact-code product images and return only visually suitable
     * dark/mid-dark office carpet variants. Image-based claims are deliberately
     * kept separate from verified fiber/pile facts.
     */
    private function rankOfficeColorVariants($products, string $apiKey)
    {
        $sampled = $products
            ->groupBy(fn ($product) => $this->normalizeSearchText((string) ($product['model'] ?? '')))
            ->flatMap(function ($group) {
                $group = $group->sortBy(fn ($product) => trim((string) ($product['code'] ?? '')))->values();
                $count = $group->count();
                if ($count <= 4) return $group;

                $indexes = array_values(array_unique([
                    0,
                    (int) round(($count - 1) / 3),
                    (int) round(2 * ($count - 1) / 3),
                    $count - 1,
                ]));

                return collect($indexes)->map(fn ($index) => $group->get($index))->filter();
            })
            ->take(28)
            ->values();

        $visualCandidates = [];
        foreach ($sampled as $product) {
            $slug = trim((string) ($product['id'] ?? ''));
            $code = trim((string) ($product['code'] ?? ''));
            if ($slug === '' || $code === '') continue;

            $image = StoreCatalog::imageDataUrlForProduct($slug);
            if (!$image) continue;

            $visualCandidates[] = [
                'product' => $product,
                'image' => $image,
                'index' => count($visualCandidates) + 1,
            ];
        }

        if ($visualCandidates === []) {
            return collect();
        }

        $prompt = <<<'PROMPT'
You are visually reviewing exact-code carpet swatch photos for a Persian flooring retailer.
The customer's request is a dark, dirt-masking carpet for an office. Inspect only what is actually visible in each swatch image.
Prefer medium-dark charcoal/grey, greige, taupe or muted brown tones with mixed/heathered or varied texture that can conceal ordinary dust and small marks. Do not automatically prefer a flat pure-black sample: lint and light dust may show on it. Avoid pale/cream/light samples.
Do not infer durability or fiber quality from the color photo. Return a relative visual suitability score for hiding everyday dirt, a darkness score, a confidence score, and a concise Persian color label. If the image does not clearly show a carpet color/sample, set confidence low.
Respond only with valid JSON in this exact shape:
{"items":[{"index":1,"album":"پونه","code":"4252","color_fa":"خاکستری زغالی ملانژ","darkness_score":0.8,"soil_masking_score":0.85,"confidence":0.9}]}
All scores are numeric 0..1. Include only images you can judge from the visible sample.
PROMPT;

        $parts = [['type' => 'text', 'text' => 'Analyze these carpet swatches. The number immediately before each image is its candidate index. Match every answer to that exact index, not to the apparent model name.']];
        foreach ($visualCandidates as $candidate) {
            $product = $candidate['product'];
            $parts[] = [
                'type' => 'text',
                'text' => 'Candidate index '.$candidate['index']
                    .' | Album: '.(string) ($product['model'] ?? '')
                    .' | Exact product code: '.(string) ($product['code'] ?? ''),
            ];
            $parts[] = ['type' => 'image_url', 'image_url' => ['url' => $candidate['image']]];
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->withHeaders([
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => 'Palaz Office Carpet Color Advisor',
                ])
                ->timeout(45)
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => config('services.openrouter.vision_model', config('services.openrouter.model', 'openrouter/free')),
                    'messages' => [
                        ['role' => 'system', 'content' => $prompt],
                        ['role' => 'user', 'content' => $parts],
                    ],
                    'max_tokens' => 1200,
                    'temperature' => 0.1,
                ]);

            if (!$response->successful()) {
                return collect();
            }

            $text = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
            $decoded = json_decode($text, true);
            if (!is_array($decoded)) {
                $first = strpos($text, '{');
                $last = strrpos($text, '}');
                if ($first !== false && $last !== false && $last > $first) {
                    $decoded = json_decode(substr($text, $first, $last - $first + 1), true);
                }
            }

            $items = data_get($decoded, 'items', []);
            if (!is_array($items)) {
                return collect();
            }

            $byIndex = collect($visualCandidates)->keyBy('index');
            return collect($items)
                ->filter(function ($item) {
                    return is_array($item)
                        && is_numeric($item['index'] ?? null)
                        && isset($item['code'])
                        && is_numeric($item['darkness_score'] ?? null)
                        && is_numeric($item['soil_masking_score'] ?? null)
                        && is_numeric($item['confidence'] ?? null)
                        && (float) $item['confidence'] >= 0.6
                        && (float) $item['darkness_score'] >= 0.45
                        && (float) $item['soil_masking_score'] >= 0.62;
                })
                ->map(function ($item) use ($byIndex) {
                    $candidate = $byIndex->get((int) $item['index']);
                    if (!$candidate) return null;

                    // Do not attach a visual result to the wrong product if the model mismatches an index and a code.
                    $expectedCode = trim((string) ($candidate['product']['code'] ?? ''));
                    $returnedCode = trim((string) ($item['code'] ?? ''));
                    if ($expectedCode === '' || $returnedCode !== $expectedCode) return null;

                    $product = $candidate['product'];
                    $product['_visual_tone_fa'] = mb_substr(trim((string) ($item['color_fa'] ?? 'رنگ تیره/میانه')), 0, 80);
                    $product['_visual_darkness_score'] = (float) $item['darkness_score'];
                    $product['_visual_soil_masking_score'] = (float) $item['soil_masking_score'];
                    $product['_visual_confidence'] = (float) $item['confidence'];
                    return $product;
                })
                ->filter()
                ->sortByDesc(fn ($product) => $product['_visual_soil_masking_score'])
                ->unique(fn ($product) => trim((string) ($product['code'] ?? '')))
                ->take(3)
                ->values();
        } catch (\Throwable $e) {
            report($e);
            return collect();
        }
    }

    private function pileHeightForChildSoftness(string $album): ?float
    {
        $album = $this->normalizeSearchText($album);
        return match ($album) {
            $this->normalizeSearchText('الماس') => 2.5,
            $this->normalizeSearchText('نیکاس') => 2.0,
            $this->normalizeSearchText('یاس') => 1.5,
            $this->normalizeSearchText('افرا') => 1.2,
            $this->normalizeSearchText('کات بی') => 1.0,
            default => null,
        };
    }

    private function calculationContext($products, float $area): string
    {
        if ($products->isEmpty() || $area <= 0) {
            return '';
        }

        $lines = $products->take(5)->map(function ($product) use ($area) {
            $attributes = is_array($product['attributes'] ?? null) ? $product['attributes'] : [];
            $type = strtolower((string) ($product['calculation_type'] ?? ''));
            $unit = mb_strtolower((string) ($product['unit'] ?? ''));
            $recordedPrice = $product['price'] ?? null;

            if ($recordedPrice === null && !isset($attributes['final_unit_price']) && !isset($attributes['base_unit_price'])) {
                return 'برآورد '.$product['name'].': قیمت در کاتالوگ ثبت نشده و باید استعلام شود.';
            }

            // قیمت واردشده به‌عنوان final_unit_price شامل مالیات است؛ مالیات را دوباره اضافه نکن.
            $vatPercent = is_numeric($attributes['vat_percent'] ?? null)
                ? max(0, (float) $attributes['vat_percent'])
                : null;
            $hasFinalPrice = is_numeric($attributes['final_unit_price'] ?? null);
            $hasBasePrice = is_numeric($attributes['base_unit_price'] ?? null);

            if ($hasFinalPrice) {
                $unitPrice = (float) $attributes['final_unit_price'];
                $priceLabel = 'قیمت ثبت‌شده با مالیاتِ درج‌شده در کاتالوگ';
            } elseif ($hasBasePrice && $vatPercent !== null) {
                $unitPrice = (float) $attributes['base_unit_price'] * (1 + $vatPercent / 100);
                $priceLabel = 'برآورد با '.$vatPercent.'٪ مالیات ثبت‌شده';
            } elseif ($hasBasePrice) {
                $unitPrice = (float) $attributes['base_unit_price'];
                $priceLabel = 'قیمت پایه ثبت‌شده؛ وضعیت مالیات در داده موجود مشخص نیست';
            } else {
                $unitPrice = (float) $recordedPrice;
                $priceLabel = 'بر اساس قیمت ثبت‌شده؛ وضعیت مالیات در داده موجود مشخص نیست';
            }

            if ($type === 'carton' || str_contains($unit, 'کارتن')) {
                $factor = (float) data_get($product, 'attributes.carton_factor', 1.777);
                if ($factor <= 0) $factor = 1.777;
                $cartons = (int) ceil($area / $factor);
                $estimated = $cartons * $unitPrice;
                return implode(' | ', [
                    'برآورد '.$product['name'],
                    'متراژ: '.number_format($area, 2).' مترمربع',
                    'پوشش هر کارتن: '.number_format($factor, 3).' مترمربع',
                    'تعداد تقریبی کارتن: '.$cartons,
                    $priceLabel,
                    'جمع تقریبی: '.number_format($estimated).' تومان',
                    'تعداد نهایی با توجه به پرت و دستورالعمل نصب تأیید شود.',
                ]);
            }

            // برای موکت رولی، بدون انتخاب طول رول و بررسی موجودی، تعداد رول دقیق قابل محاسبه نیست.
            $estimated = $area * $unitPrice;
            $extra = ($type === 'roll' || str_contains($unit, 'رول') || str_contains($unit, 'طاقه'))
                ? 'این فقط برآورد بر مبنای متراژ و قیمت واحد است؛ طول رول، برش، پرت و موجودی باید پیش از سفارش تأیید شود.'
                : 'این برآورد بر مبنای متراژ و قیمت واحد است؛ پرت و شرایط اجرا ممکن است مبلغ نهایی را تغییر دهد.';

            return implode(' | ', [
                'برآورد '.$product['name'],
                'متراژ: '.number_format($area, 2).' مترمربع',
                'قیمت واحد مبنا: '.number_format($unitPrice).' تومان',
                $priceLabel,
                'جمع تقریبی: '.number_format($estimated).' تومان',
                $extra,
            ]);
        })->implode("\n");

        return "\n\nبرآورد اولیه برای ".$area." مترمربع بر اساس داده‌های ثبت‌شده کاتالوگ:\n".$lines
            ."\nقانون: قیمت و مالیات را حدس نزن؛ قیمت نهایی، موجودی، پرت، برش و شرایط اجرا باید پیش از ثبت سفارش تأیید شود.";
    }

    private function filterProductsForAdvisorNeed(string $text, $products)
    {
        $query = $this->normalizeSearchText($text);
        $categorySlugs = [];

        // دسته درخواست‌شده باید واقعاً در کاتالوگ موجود باشد؛ در غیر این صورت
        // هرگز کل کاتالوگ را به‌عنوان جایگزینِ نامرتبط برنمی‌گردانیم.
        if (preg_match('/موکت\\s*(?:تایل|تایلی)|تایل\\s*موکت/u', $query)) {
            $categorySlugs = ['carpet_tile', 'carpet-tile', 'tile-carpet'];
        } elseif (preg_match('/موکت/u', $query)) {
            $categorySlugs = ['carpet'];
        } elseif (preg_match('/لمینت|پارکت/u', $query)) {
            $categorySlugs = ['laminate', 'laminate_flooring', 'parquet'];
        } elseif (preg_match('/\\bspc\\b|اس\\s*پی\\s*سی/u', $query)) {
            $categorySlugs = ['spc'];
        } elseif (preg_match('/کاغذ\\s*دیواری/u', $query)) {
            $categorySlugs = ['wallpaper'];
        } elseif (preg_match('/چمن(?:\\s*مصنوعی)?/u', $query)) {
            $categorySlugs = ['grass', 'artificial_grass'];
        } elseif (preg_match('/گارد|اسپاگتی/u', $query)) {
            $categorySlugs = ['garde', 'spaghetti'];
        }

        if ($categorySlugs === []) {
            return $products->values();
        }

        return $products->filter(function ($product) use ($categorySlugs) {
            $category = $this->normalizeSearchText((string) ($product['category'] ?? ''));
            return in_array($category, $categorySlugs, true);
        })->values();
    }

    private function matchProducts(string $message, $products)
    {
        $query = $this->normalizeSearchText($message);
        if ($query === '' || $products->isEmpty()) {
            return collect();
        }

        // واژه‌های عمومی مکالمه نباید باعث شوند محصولات به‌ظاهر مرتبط امتیاز بگیرند.
        $stopWords = [
            'برای','میخوام','میخواهم','میخام','دنبال','مناسب','گزینه','انتخاب','پیشنهاد',
            'خانه','خانگی','منزل','پذیرایی','اتاق','خواب','راهرو','آشپزخانه','دفتر',
            'اداری','اداره','هتل','لابی','نمازخانه','فضا','محل','کار','متر','مترمربع',
            'مربع','متراژ','مساحت','حدود','تقریبا','تقریباً','دارم','است','هست','باشه',
            'موکت','لمینت','پارکت','کفپوش','کف','قیمت','بودجه','اقتصادی','ارزان',
            'گران','دوام','پرتردد','نرمی','راحتی','رنگ','طرح','کیفیت','میباشد',
        ];
        $tokens = collect(preg_split('/\\s+/u', $query))
            ->map(fn ($token) => preg_replace('/^[^\\p{L}\\p{N}]+|[^\\p{L}\\p{N}]+$/u', '', $token))
            ->filter(fn ($token) => mb_strlen($token) >= 3 && !in_array($token, $stopWords, true) && !is_numeric($token))
            ->unique()
            ->values();

        $ranked = $products
            ->map(function ($product) use ($query, $tokens) {
                $name = $this->normalizeSearchText((string) ($product['name'] ?? ''));
                $model = $this->normalizeSearchText((string) ($product['model'] ?? ''));
                $code = $this->normalizeSearchText((string) ($product['code'] ?? ''));
                $description = $this->normalizeSearchText((string) ($product['description'] ?? ''));
                $tone = $this->normalizeSearchText((string) ($product['tone'] ?? ''));
                $haystack = implode(' ', array_filter([$name, $model, $code, $description, $tone]));
                $score = 0;

                if ($code !== '' && preg_match('/(?<![\\p{L}\\p{N}])'.preg_quote($code, '/').'(?![\\p{L}\\p{N}])/u', $query)) {
                    $score += 100;
                }
                if ($model !== '' && mb_strlen($model) >= 2 && str_contains($query, $model)) {
                    $score += 70;
                }
                if ($name !== '' && str_contains($query, $name)) {
                    $score += 50;
                }

                foreach ($tokens as $token) {
                    if (str_contains($haystack, $token)) {
                        $score += 5;
                    }
                }

                // اطلاعات واقعی و قابل نمایش اولویت دارند؛ این امتیاز ادعای کیفیت محصول نیست.
                if (!empty($product['image'])) $score += 2;
                if (!empty($product['model'])) $score += 2;
                if (!empty($product['code'])) $score += 1;
                if (($product['price'] ?? null) !== null) $score += 1;

                return ['product' => $product, 'score' => $score];
            })
            ->sortByDesc('score')
            ->pluck('product')
            ->values();

        // اگر مشتری اسم/کد خاصی نگفته، چند گزینه متنوع از همان دسته مجاز را نشان بده؛
        // تکرار کدهای یک آلبوم را تا حد ممکن کم می‌کنیم.
        $specificMatch = $tokens->isNotEmpty()
            && $ranked->take(3)->contains(function ($product) use ($query) {
                $model = $this->normalizeSearchText((string) ($product['model'] ?? ''));
                $code = $this->normalizeSearchText((string) ($product['code'] ?? ''));
                $name = $this->normalizeSearchText((string) ($product['name'] ?? ''));
                return ($code !== '' && str_contains($query, $code))
                    || ($model !== '' && mb_strlen($model) >= 2 && str_contains($query, $model))
                    || ($name !== '' && mb_strlen($name) >= 4 && str_contains($query, $name));
            });

        $candidates = $specificMatch
            ? $ranked->filter(function ($product) use ($query, $tokens) {
                $haystack = $this->normalizeSearchText(implode(' ', array_filter([
                    $product['name'] ?? '', $product['model'] ?? '',
                    $product['code'] ?? '', $product['description'] ?? '', $product['tone'] ?? '',
                ])));
                $code = $this->normalizeSearchText((string) ($product['code'] ?? ''));
                $model = $this->normalizeSearchText((string) ($product['model'] ?? ''));
                return ($code !== '' && str_contains($query, $code))
                    || ($model !== '' && mb_strlen($model) >= 2 && str_contains($query, $model))
                    || $tokens->contains(fn ($token) => str_contains($haystack, $token));
            })->values()
            : $ranked;

        // کد دقیق بر نام آلبوم اولویت دارد؛ اگر کد گفته شده، فقط همان کد را برگردان.
        $exactCodeMatches = $ranked->filter(function ($product) use ($query) {
            $code = $this->normalizeSearchText((string) ($product['code'] ?? ''));
            return $code !== '' && preg_match('/(?<![\\p{L}\\p{N}])'.preg_quote($code, '/').'(?![\\p{L}\\p{N}])/u', $query);
        })->values();
        if ($exactCodeMatches->isNotEmpty()) {
            return $exactCodeMatches->take(3)->values();
        }

        // اگر کاربر نام آلبوم را گفته، کدهای مختلف همان آلبوم را نگه دار؛
        // مرزبندی واژه مانع اشتباه گرفتن «یاس» با «یاسمن» می‌شود.
        $albumRequested = $ranked->contains(function ($product) use ($query) {
            $model = $this->normalizeSearchText((string) ($product['model'] ?? ''));
            return $model !== '' && mb_strlen($model) >= 2
                && preg_match('/(?<![\\p{L}\\p{N}])'.preg_quote($model, '/').'(?![\\p{L}\\p{N}])/u', $query);
        });
        if ($albumRequested) {
            return $candidates
                ->filter(function ($product) use ($query) {
                    $model = $this->normalizeSearchText((string) ($product['model'] ?? ''));
                    return $model !== ''
                        && preg_match('/(?<![\\p{L}\\p{N}])'.preg_quote($model, '/').'(?![\\p{L}\\p{N}])/u', $query);
                })
                ->unique(fn ($product) => (string) ($product['code'] ?? $product['id'] ?? $product['name'] ?? ''))
                ->take(8)
                ->values();
        }

        return $candidates
            ->unique(function ($product) {
                $model = trim((string) ($product['model'] ?? ''));
                return $model !== '' ? 'model:'.$this->normalizeSearchText($model) : 'product:'.($product['id'] ?? $product['code'] ?? $product['name'] ?? '');
            })
            ->take(8)
            ->values();
    }

    private function normalizeSearchText(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = str_replace(['ي','ى','ك','ة','ۀ','ؤ','إ','أ','ٱ'], ['ی','ی','ک','ه','ه','و','ا','ا','ا'], $text);
        // کدها ممکن است با ارقام فارسی یا عربی تایپ شوند؛ همه را برای جست‌وجوی دقیق یکسان می‌کنیم.
        $text = strtr($text, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
        // نوشتار لاتین رایج کات B و کات C را به نام رسمی آلبوم تبدیل کن.
        $text = preg_replace('/کات\\s*c/u', 'کات سی', $text);
        $text = preg_replace('/کات\\s*b/u', 'کات بی', $text);
        return preg_replace('/[\\x{200c}\\s]+/u', ' ', $text) ?? $text;
    }

    private function fiberTypeForAlbum(string $album): ?string
    {
        $album = $this->normalizeSearchText($album);
        $polypropylene = ['کات سی', 'کات بی', 'زیبا', 'افرا', 'نیکاس', 'پامچال', 'باران', 'پونه', 'نعنا', 'نیلوفر', 'شبنم'];
        $polyamide = ['یاسمن', 'یاس', 'الماس', 'آسا', 'پتینه', 'راش', 'ونوس', 'دالیا', 'آیلین'];

        foreach ($polypropylene as $name) {
            if ($album === $this->normalizeSearchText($name)) return 'پلی‌پروپیلن';
        }
        foreach ($polyamide as $name) {
            if ($album === $this->normalizeSearchText($name)) return 'پلی‌آمید';
        }

        return null;
    }

    private function pileTypeForAlbum(string $album): ?string
    {
        $album = $this->normalizeSearchText($album);
        // مدل‌های هتلی VIP با بافت کات پایل فشرده ثبت می‌شوند.
        if (in_array($album, ['ونوس', 'دالیا', 'آیلین'], true)) return 'کات پایل فشرده';
        $loopPile = ['پامچال', 'پونه', 'نعنا', 'نیلوفر', 'شبنم', 'آسا', 'پتینه', 'راش'];
        $cutPile = ['کات سی', 'کات بی', 'زیبا', 'افرا', 'نیکاس', 'باران', 'یاسمن', 'یاس', 'الماس', 'ونوس', 'دالیا'];

        foreach ($loopPile as $name) {
            if ($album === $this->normalizeSearchText($name)) return 'لوپ پایل';
        }
        foreach ($cutPile as $name) {
            if ($album === $this->normalizeSearchText($name)) return 'کات پایل';
        }

        return null;
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

    private function fallback(string $message, array $history = [], $products = null): string
    {
        $t = mb_strtolower($message);
        $conversationText = collect($history)
            ->filter(fn ($m) => ($m['role'] ?? '') === 'user')
            ->pluck('content')
            ->push($message)
            ->implode(' ');
        $conversationText = $this->appendImpliedAreaAnswer($conversationText, $message, $history);
        $normalizedConversation = $this->normalizeSearchText($conversationText);
        $hasSpace = preg_match('/خانه|خانگی|منزل|پذیرایی|اتاق|راهرو|آشپزخانه|محل کار|اداری|اداره|دفتر|هتل|لابی|نمازخانه/u', $normalizedConversation);
        $hasArea = $this->extractAdvisorArea($conversationText) !== null;
        $hasCovering = preg_match('/موکت|لمینت|اس پی سی|spc|پارکت|چمن|کاغذ دیواری|گارد/u', $normalizedConversation);

        // حتی اگر سرویس هوش مصنوعی در دسترس نباشد، پس از تکمیل نیاز مشتری
        // از کاتالوگ واقعی چند گزینه پیشنهاد بده؛ پاسخ عمومی را تکرار نکن.
        if ($hasSpace && $hasArea && $hasCovering && $products instanceof \Illuminate\Support\Collection) {
            $advisorProducts = $this->filterProductsForAdvisorNeed($conversationText, $products);
            $matches = $this->matchProducts($conversationText, $advisorProducts)->take(3);
            // اگر تطبیق قابل‌اتکا نداریم، محصول تصادفی پیشنهاد نمی‌دهیم.
            if ($matches->isNotEmpty()) {
                $lines = $matches->map(function ($product) {
                    $details = array_filter([
                        $product['name'] ?? null,
                        !empty($product['model']) ? 'مدل/آلبوم '.$product['model'] : null,
                        !empty($product['code']) ? 'کد '.$product['code'] : null,
                        ($product['price'] ?? null) !== null
                            ? 'قیمت ثبت‌شده '.number_format((float) $product['price']).' تومان به ازای '.($product['unit'] ?? 'واحد فروش')
                            : 'قیمت استعلامی',
                    ]);
                    return '• '.implode('؛ ', $details);
                })->implode("\n");

                return "با توجه به اطلاعاتی که فرمودید، این گزینه‌ها از کاتالوگ فعلی پالاز برای بررسی اولیه در دسترس‌اند:\n".$lines."\nبرای انتخاب نهایی، طرح/رنگ و شرایط اجرا را هم بررسی می‌کنیم. قیمت‌های ثبت‌شده ممکن است نیاز به تأیید نهایی داشته باشند.";
            }

            return 'نیازتان را متوجه شدم، اما در فهرست فعلی محصولی که بتوانم با اطمینان به همین درخواست مرتبط کنم پیدا نکردم. اگر نام آلبوم، کد محصول یا اولویت اصلی‌تان مثل دوام، نرمی یا شست‌وشوی آسان را بفرمایید، دقیق‌تر محدودش می‌کنم؛ قیمت یا مشخصات تأییدنشده را حدس نمی‌زنم.';
        }
        $lastAssistant = collect($history)
            ->filter(fn ($m) => ($m['role'] ?? '') === 'assistant')
            ->last();
        $previousAssistantText = mb_strtolower((string) data_get($lastAssistant, 'content', ''));
        if (preg_match('/بودجه|هزینه|مبلغ/u', $previousAssistantText)) {
            return 'ممنون، بودجه‌تان را در نظر می‌گیرم. برای اینکه پاسخ تکراری ندهم، فقط موردی را بفرمایید که هنوز مشخص نشده است: فضا، متراژ یا نوع پوشش.';
        }
        if (preg_match('/سلام|درود|وقت بخیر/u', $t)) {
            return 'حتماً. بگویید برای چه فضایی و چه نوع پوششی دنبال گزینه مناسب هستید؟';
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

    private function productCards($products, string $conversationText): array
    {
        $query = $this->normalizeSearchText($conversationText);
        // کارت محصول فقط وقتی نمایش داده شود که مشتری نوع پوشش را واقعاً مشخص کرده باشد.
        if (!preg_match('/موکت|لمینت|پارکت|اس\\s*پی\\s*سی|spc|فرش[\\s‌-]*گونه|چمن|کاغذ\\s*دیواری|گارد|اسپاگتی/u', $query)) {
            return [];
        }

        return $products->take(3)->map(function ($product) {
            $id = (string) ($product['id'] ?? '');
            if ($id === '') return null;

            return [
                'id' => $id,
                'name' => (string) ($product['name'] ?? 'محصول پالاز'),
                'model' => (string) ($product['model'] ?? ''),
                'code' => (string) ($product['code'] ?? ''),
                'price' => ($product['price'] ?? null) !== null ? (float) $product['price'] : null,
                'price_label' => ($product['price'] ?? null) !== null
                    ? number_format((float) $product['price']).' تومان / '.((string) ($product['unit'] ?? 'واحد فروش'))
                    : 'قیمت نیازمند استعلام',
                'unit' => (string) ($product['unit'] ?? ''),
                'image' => $product['image'] ?? null,
                'url' => route('product', ['id' => $id]),
            ];
        })->filter()->values()->all();
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
