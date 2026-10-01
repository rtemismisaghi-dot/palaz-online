<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('product_media', 'disk')) {
            Schema::table('product_media', function (Blueprint $table) {
                $table->string('disk', 32)->default('public')->after('product_id');
            });
        }

        if (!Schema::hasColumn('product_media', 'source_url')) {
            Schema::table('product_media', function (Blueprint $table) {
                $table->text('source_url')->nullable()->after('path');
            });
        }

        Schema::table('product_media', function (Blueprint $table) {
            $table->index(['product_id', 'is_cover', 'sort_order'], 'product_media_product_cover_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::table('product_media', function (Blueprint $table) {
            $table->dropIndex('product_media_product_cover_sort_idx');
            if (Schema::hasColumn('product_media', 'source_url')) {
                $table->dropColumn('source_url');
            }
            if (Schema::hasColumn('product_media', 'disk')) {
                $table->dropColumn('disk');
            }
        });
    }
};
