<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        // Catalog fields and indexes are already handled safely by
        // 2026_09_28_000004_harden_catalog_admin.php.
        // Keep this later migration as a no-op so existing databases
        // do not attempt to add the same columns a second time.
    }

    public function down(): void
    {
        // Intentionally no-op.
    }
};
