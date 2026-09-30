<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        DB::table('categories')
            ->where('slug', 'spc')
            ->update([
                'name' => 'فرش‌گونه',
                'eyebrow' => 'مجموعه فرش‌گونه پالاز',
                'tone' => 'فرش‌گونه',
                'updated_at' => $now,
            ]);

        if (! DB::table('categories')->where('slug', 'wallpaper')->exists()) {
            DB::table('categories')->insert([
                'slug' => 'wallpaper',
                'name' => 'کاغذ دیواری',
                'eyebrow' => 'طرح‌ها و رنگ‌های متنوع',
                'tone' => 'کاغذ دیواری',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('categories')
            ->where('slug', 'spc')
            ->update([
                'name' => 'SPC',
                'eyebrow' => 'کفپوش‌های مقاوم',
                'tone' => 'SPC',
                'updated_at' => now(),
            ]);
    }
};
