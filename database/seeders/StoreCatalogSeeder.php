<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPricingRule;
use Illuminate\Database\Seeder;

class StoreCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug'=>'carpet','name'=>'موکت','eyebrow'=>'CARPET','tone'=>'sand','sort_order'=>1],
            ['slug'=>'laminate','name'=>'لمینیت','eyebrow'=>'LAMINATE','tone'=>'wood','sort_order'=>2],
            ['slug'=>'spc','name'=>'SPC','eyebrow'=>'SPC FLOOR','tone'=>'stone','sort_order'=>3],
            ['slug'=>'wallpaper','name'=>'کاغذدیواری','eyebrow'=>'WALLPAPER','tone'=>'rose','sort_order'=>4],
            ['slug'=>'tile','name'=>'موکت تایل','eyebrow'=>'CARPET TILE','tone'=>'dark','sort_order'=>5],
            ['slug'=>'grass','name'=>'چمن مصنوعی','eyebrow'=>'ARTIFICIAL GRASS','tone'=>'green','sort_order'=>6],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(['slug' => $data['slug']], $data);
        }

        $products = [
            ['category'=>'carpet','slug'=>'carpet-arta','name'=>'موکت پالاز مدل آرتا','tone'=>'sand','description'=>'موکت بافت‌دار مناسب فضاهای مسکونی و اداری.'],
            ['category'=>'carpet','slug'=>'carpet-shahkar','name'=>'موکت پالاز مدل شاهکار','tone'=>'rose','description'=>'انتخابی کلاسیک با رنگ‌بندی متنوع.'],
            ['category'=>'laminate','slug'=>'laminate-classic','name'=>'لمینیت پالاز مدل Classic','tone'=>'wood','description'=>'سطح چوبی با ظاهر طبیعی و اجرای سریع.'],
            ['category'=>'spc','slug'=>'spc-stone','name'=>'کفپوش SPC پالاز مدل Stone','tone'=>'stone','description'=>'کفپوش مقاوم برای پروژه‌های مدرن.'],
            ['category'=>'wallpaper','slug'=>'wallpaper-line','name'=>'کاغذدیواری پالاز مدل Line','tone'=>'rose','description'=>'طرح مینیمال برای دیوارهای شاخص.'],
            ['category'=>'tile','slug'=>'tile-grid','name'=>'موکت تایل پالاز مدل Grid','tone'=>'dark','description'=>'ماژولار، کاربردی و مناسب فضاهای پرتردد.'],
            ['category'=>'grass','slug'=>'grass-garden','name'=>'چمن مصنوعی پالاز مدل Garden','tone'=>'green','description'=>'ظاهر طبیعی برای تراس، حیاط و فضای تجاری.'],
        ];

        foreach ($products as $data) {
            $category = Category::where('slug', $data['category'])->firstOrFail();

            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price' => null,
                    'unit' => $data['category'] === 'carpet' ? 'مترمربع' : 'تماس برای قیمت',
                    'tone' => $data['tone'],
                    'is_active' => true,
                ]
            );

            if ($data['category'] === 'carpet') {
                ProductPricingRule::updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'calculation_type' => 'roll',
                        'unit' => 'مترمربع',
                        'waste_percent' => 0,
                        'parameters' => [
                            'width' => 3,
                            'min_length' => 1,
                            'max_length' => 15,
                        ],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
