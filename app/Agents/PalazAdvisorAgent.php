<?php

namespace App\Agents;

use App\Support\StoreCatalog;
use Illuminate\Support\Facades\Http;

final class PalazAdvisorAgent
{
    public function reply(string $message, array $history = []): array
    {
        $message = trim($message);
        $history = collect($history)
            ->filter(fn ($m) => in_array($m['role'] ?? null, ['user', 'assistant'], true))
            ->map(fn ($m) => ['role' => $m['role'], 'content' => mb_substr((string) ($m['content'] ?? ''), 0, 1200)])
            ->take(-10)->values()->all();

        $catalog = collect(StoreCatalog::products())
            ->map(fn ($p) => implode(' | ', array_filter([
                'نام: '.$p['name'],
                'دسته: '.($p['category'] ?? ''),
                'قیمت: '.($p['price'] !== null ? number_format((float)$p['price']).' تومان' : 'استعلامی'),
                'واحد: '.($p['unit'] ?? ''),
                'رنگ/تون: '.($p['tone'] ?? ''),
                'توضیح: '.($p['description'] ?? ''),
            ])))->take(100)->implode("\n");

        $apiKey = (string) config('services.openrouter.key');

        if ($apiKey !== '') {
            $system = <<<'PROMPT'
تو «مشاور هوشمند پالاز» هستی؛ یک مشاور فارسی‌زبان برای انتخاب، مقایسه و برنامه‌ریزی خرید و اجرای محصولات پالاز.
لحن: صمیمی، حرفه‌ای، کوتاه و طبیعی؛ فروشنده اصراری نباش.
اول نیاز مشتری را بفهم: فضا، متراژ، سبک/اولویت، بودجه و محدودیت‌ها. در هر نوبت حداکثر دو سؤال ضروری بپرس.
فقط درباره محصول و قیمت از کاتالوگ داده‌شده استفاده کن و هرگز قیمت، موجودی یا مشخصات را حدس نزن.
اگر اطلاعات کافی است، 2 تا 3 گزینه را با دلیل کوتاه مقایسه کن.
اگر کاربر درباره اندازه‌گیری، نصب، طراحی یا محاسبه پرسید، مسیر خدمات پالاز را پیشنهاد کن.
همیشه فارسی پاسخ بده مگر نام محصول/برند/کد ذاتاً لاتین باشد.
اگر سؤال خارج از حوزه پالاز است، کوتاه و شفاف بگو در این حوزه کمکت می‌کنی.
کاتالوگ فعلی:
PROMPT;
            $payload = array_merge(
                [['role' => 'system', 'content' => $system."\n".$catalog]],
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
                    return ['reply' => $text, 'mode' => 'ai', 'actions' => $this->actions($message)];
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return ['reply' => $this->fallback($message), 'mode' => 'catalog', 'actions' => $this->actions($message)];
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
