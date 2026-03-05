<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
    {
        // For MySQL, altering enum requires raw SQL
        DB::statement("ALTER TABLE `invoice_items` MODIFY `type` ENUM('registration', 'consultation', 'lab', 'medicine', 'bed', 'service') NOT NULL DEFAULT 'registration'");
    }

    public function down(): void
    {
        // Rollback: remove 'service' from enum
        DB::statement("ALTER TABLE `invoice_items` MODIFY `type` ENUM('registration', 'consultation', 'lab', 'medicine', 'bed') NOT NULL DEFAULT 'registration'");
    }
};
