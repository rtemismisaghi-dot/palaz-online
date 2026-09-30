<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('attributes')->nullable()->after('tone');
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->index(['category_id', 'is_active']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id', 'is_active']);
            $table->dropIndex(['is_featured']);
            $table->dropColumn(['attributes', 'is_featured']);
        });
    }
};
