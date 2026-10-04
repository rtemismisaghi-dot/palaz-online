<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPricingRule;
use Illuminate\Database\Seeder;

class CarpetCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('docs/palaz-catalog-extraction-batch-2026-09-27.json');
        if (! is_file($path)) {
            $this->command?->error('Catalog JSON not found.');
            return;
        }

        $catalog = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $items = collect($catalog['products'] ?? [])
            ->where('category', 'موکت')
            ->filter(fn ($item) => filled($item['code'] ?? null));

        $category = Category::updateOrCreate(
            ['slug' => 'carpet'],
            [
                'name' => 'موکت',
                'eyebrow' => 'کالکشن موکت پالاز',
                'tone' => 'موکت',
                'is_active' => true,
                'sort_order' => 10,
            ]
        );

        foreach ($items as $item) {
            $code = trim((string) $item['code']);
            $album = trim((string) ($item['album'] ?? ''));
            $product = Product::updateOrCreate(
                ['slug' => 'carpet-'.$code],
                [
                    'category_id' => $category->id,
                    'name' => trim((string) ($item['product_name'] ?? 'موکت پالاز')),
                    'description' => $album ? 'آلبوم '.$album.' | کد '.$code : 'کد '.$code,
                    'price' => isset($item['final_unit_price']) ? (float) $item['final_unit_price'] : null,
                    'unit' => 'متر مربع',
                    'tone' => 'موکت',
                    'attributes' => [
                        'code' => $code,
                        'album' => $album,
                        'base_unit_price' => $item['base_unit_price'] ?? null,
                        'final_unit_price' => $item['final_unit_price'] ?? null,
                        'vat_percent' => $item['vat_percent'] ?? null,
                        'catalog_source' => $item['source_url'] ?? null,
                        'stock_type' => 'roll',
                        'roll_width' => 3,
                        'roll_lengths' => range(1, 15),
                    ],
                    'is_active' => true,
                    'is_featured' => false,
                ]
            );

            ProductPricingRule::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'calculation_type' => 'roll',
                    'unit' => 'm²',
                    'waste_percent' => 0,
                    'parameters' => [
                        'width' => 3,
                        'lengths' => range(1, 15),
                        'inventory_unit' => 'roll',
                        'sale_unit' => 'm²',
                    ],
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('Imported '.$items->count().' carpet products.');
    }
}
