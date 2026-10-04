<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('customer_users') && !Schema::hasColumn('customer_users', 'password')) {
            Schema::table('customer_users', function (Blueprint $table) {
                $table->string('password')->nullable()->after('mobile');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customer_users') && Schema::hasColumn('customer_users', 'password')) {
            Schema::table('customer_users', function (Blueprint $table) {
                $table->dropColumn('password');
            });
        }
    }
};
