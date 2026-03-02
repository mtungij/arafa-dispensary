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
        Schema::create('registration_fees', function (Blueprint $table) {
            $table->id();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->enum('patient_type', ['cash', 'insurance']);
    $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['company_id', 'patient_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_fees');
    }
};
