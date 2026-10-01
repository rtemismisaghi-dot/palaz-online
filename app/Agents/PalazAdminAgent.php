<?php

namespace App\Agents;

use App\Models\Product;
use Illuminate\Support\Facades\Http;

final class PalazAdminAgent
{
    public function interpret(string $message): array
    {
        $message = trim($message);
        $products = Product::query()
            ->select(['id','name','slug','price','unit','is_active'])
            ->latest('id')->limit(150)->get()
            ->map(fn ($p) => [
                'id'=>$p->id,'name'=>$p->name,'code'=>$p->slug,
                'price'=>$p->price !== null ? (float)$p->price : null,
                'unit'=>$p->unit,'active'=>(bool)$p->is_active,
            ])->values()->all();

        $key = (string) config('services.openrouter.key');
        if ($key !== '') {
            $system = <<<'PROMPT'
تو Agent مدیریت کاتالوگ پالاز هستی. فقط عملیات روی محصولات فروشگاه را تحلیل کن.
پاسخ فقط JSON معتبر باشد:
{"reply":"پاسخ فارسی کوتاه","action":null|{"type":"set_price","product_id":1,"price":123,"confirm_required":true}|{"type":"set_active","product_id":1,"active":true,"confirm_required":true}|{"type":"delete_product","product_id":1,"confirm_required":true}|{"type":"find_product","product_id":1,"confirm_required":false}}
قواعد:
- هرگز محصول یا قیمت را حدس نزن.
- اگر چند محصول ممکن است، action را null کن و در reply سؤال روشن‌کننده بپرس.
- تغییر قیمت، فعال/غیرفعال و حذف همیشه confirm_required=true است.
- اگر کاربر فقط جستجو یا وضعیت محصول را خواست، find_product مجاز است.
- عملیات افزودن محصول و عکس فعلاً فقط باید توضیح داده شود و action اجرایی تولید نشود.
- برای قیمت عدد را بدون جداکننده برگردان.
کاتالوگ:
PROMPT;
            try {
                $response = Http::withToken($key)->acceptJson()
                    ->withHeaders(['HTTP-Referer'=>config('app.url'),'X-Title'=>'Palaz Admin Agent'])
                    ->timeout(30)->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model'=>config('services.openrouter.model','openrouter/free'),
                        'messages'=>[
                            ['role'=>'system','content'=>$system."\n".json_encode($products, JSON_UNESCAPED_UNICODE)],
                            ['role'=>'user','content'=>$message],
                        ],
                        'max_tokens'=>450,'temperature'=>0.1,
                        'response_format'=>['type'=>'json_object'],
                    ]);
                $raw=trim((string)data_get($response->json(),'choices.0.message.content',''));
                if ($response->successful() && $raw !== '') {
                    $decoded=json_decode($raw,true);
                    if (is_array($decoded)) return $this->normalize($decoded,$products);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $this->fallback($message,$products);
    }

    private function normalize(array $data, array $products): array
    {
        $action=$data['action'] ?? null;
        if (is_array($action) && isset($action['product_id'])) {
            $exists=collect($products)->firstWhere('id',(int)$action['product_id']);
            if (!$exists) $action=null;
        }
        return [
            'reply'=>(string)($data['reply'] ?? 'دستور را بررسی کردم.'),
            'action'=>$action,
        ];
    }

    private function fallback(string $message, array $products): array
    {
        foreach ($products as $p) {
            if (mb_strtolower($message) === mb_strtolower((string)$p['code']) ||
                mb_strpos($message,(string)$p['code']) !== false ||
                mb_strpos($message,(string)$p['name']) !== false) {
                return ['reply'=>"محصول «{$p['name']}» با کد {$p['code']} پیدا شد. قیمت: ".($p['price'] === null ? 'استعلامی' : number_format($p['price']))." {$p['unit']}. وضعیت: ".($p['active']?'فعال':'غیرفعال').".",'action'=>['type'=>'find_product','product_id'=>$p['id'],'confirm_required'=>false]];
            }
        }
        return ['reply'=>'محصول موردنظر پیدا نشد. کد یا نام دقیق محصول را بفرست.','action'=>null];
    }
}
