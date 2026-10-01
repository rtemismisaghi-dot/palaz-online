<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Hash;
use Illuminate\\Support\\Facades\\Schema;
use Illuminate\\Support\\Facades\\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admin_users') || !Schema::hasColumn('admin_users', 'mobile')) {
            return;
        }

        DB::table('admin_users')->updateOrInsert(
            ['mobile' => '09209075332'],
            [
                'name' => 'مدیر تست پالاز',
                'password' => Hash::make('123456'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('admin_users')->where('mobile', '09209075332')->delete();
    }
};
