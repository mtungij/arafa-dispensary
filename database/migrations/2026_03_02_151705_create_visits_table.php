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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
     $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
    $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
    $table->enum('visit_type', ['opd','short_stay'])->default('opd');
  $table->string('current_department')
      ->default('registration');
    $table->enum('status', ['waiting_payment','waiting_doctor','consultation','waiting_lab','medicine','completed'])->default('waiting_payment');
    $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
