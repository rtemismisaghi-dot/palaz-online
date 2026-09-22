<?php

namespace App\Support;

final class StoreCatalog
{
    public static function categories(): array
    {
        return [
            'carpet' => ['title' => 'موکت', 'eyebrow' => 'CARPET', 'tone' => 'sand'],
            'laminate' => ['title' => 'لمینیت', 'eyebrow' => 'LAMINATE', 'tone' => 'wood'],
            'spc' => ['title' => 'SPC', 'eyebrow' => 'SPC FLOOR', 'tone' => 'stone'],
            'wallpaper' => ['title' => 'کاغذدیواری', 'eyebrow' => 'WALLPAPER', 'tone' => 'rose'],
            'tile' => ['title' => 'موکت تایل', 'eyebrow' => 'CARPET TILE', 'tone' => 'dark'],
            'grass' => ['title' => 'چمن مصنوعی', 'eyebrow' => 'ARTIFICIAL GRASS', 'tone' => 'green'],
        ];
    }

    public static function products(): array
    {
        return [
            ['id'=>'carpet-arta','category'=>'carpet','name'=>'موکت پالاز مدل آرتا','price'=>null,'unit'=>'تماس برای قیمت','tone'=>'sand','description'=>'موکت بافت‌دار مناسب فضاهای مسکونی و اداری.'],
            ['id'=>'carpet-shahkar','category'=>'carpet','name'=>'موکت پالاز مدل شاهکار','price'=>null,'unit'=>'تماس برای قیمت','tone'=>'rose','description'=>'انتخابی کلاسیک با رنگ‌بندی متنوع.'],
            ['id'=>'laminate-classic','category'=>'laminate','name'=>'لمینیت پالاز مدل Classic','price'=>null,'unit'=>'تماس برای قیمت','tone'=>'wood','description'=>'سطح چوبی با ظاهر طبیعی و اجرای سریع.'],
            ['id'=>'spc-stone','category'=>'spc','name'=>'کفپوش SPC پالاز مدل Stone','price'=>null,'unit'=>'تماس برای قیمت','tone'=>'stone','description'=>'کفپوش مقاوم برای پروژه‌های مدرن.'],
            ['id'=>'wallpaper-line','category'=>'wallpaper','name'=>'کاغذدیواری پالاز مدل Line','price'=>null,'unit'=>'تماس برای قیمت','tone'=>'rose','description'=>'طرح مینیمال برای دیوارهای شاخص.'],
            ['id'=>'tile-grid','category'=>'tile','name'=>'موکت تایل پالاز مدل Grid','price'=>null,'unit'=>'تماس برای قیمت','tone'=>'dark','description'=>'ماژولار، کاربردی و مناسب فضاهای پرتردد.'],
            ['id'=>'grass-garden','category'=>'grass','name'=>'چمن مصنوعی پالاز مدل Garden','price'=>null,'unit'=>'تماس برای قیمت','tone'=>'green','description'=>'ظاهر طبیعی برای تراس، حیاط و فضای تجاری.'],
        ];
    }

    public static function find(string $id): ?array
    {
        foreach (self::products() as $product) {
            if ($product['id'] === $id) return $product;
        }
        return null;
    }

    public static function byCategory(?string $category): array
    {
        return array_values(array_filter(self::products(), fn ($p) => !$category || $p['category'] === $category));
    }

    public static function search(?string $query): array
    {
        $query = trim((string) $query);
        if ($query === '') return self::products();
        return array_values(array_filter(self::products(), fn ($p) =>
            str_contains(mb_strtolower($p['name']), mb_strtolower($query)) ||
            str_contains(mb_strtolower($p['description']), mb_strtolower($query))
        ));
    }
}
