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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
     $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
    $table->decimal('total', 12, 2)->default(0);
    $table->decimal('insurance_amount', 12, 2)->default(0);
    $table->decimal('patient_amount', 12, 2)->default(0);
    $table->string('status')->default('unpaid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
