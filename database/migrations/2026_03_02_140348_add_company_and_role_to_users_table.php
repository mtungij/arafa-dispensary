<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
         $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
           $table->enum('role', [
            'admin',
            'pharmacist',
            'doctor',
            'technician'
        ])->default('admin')->after('password');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->string('role')->default('user'); // roles: admin, user, etc.
        });
    }
};
