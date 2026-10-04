<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'attributes')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('attributes')->nullable()->after('tone');
            });
        }

        if (! Schema::hasColumn('products', 'is_featured')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_featured')->default(false)->after('is_active');
            });
        }

        if (! Schema::hasTable('product_media')) {
            Schema::create('product_media', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('path');
                $table->string('alt')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_cover')->default(false);
                $table->timestamps();

                $table->index(['product_id', 'sort_order']);
                $table->index(['product_id', 'is_cover']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_media');

        $columns = [];

        if (Schema::hasColumn('products', 'attributes')) {
            $columns[] = 'attributes';
        }

        if (Schema::hasColumn('products', 'is_featured')) {
            $columns[] = 'is_featured';
        }

        if ($columns) {
            Schema::table('products', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
