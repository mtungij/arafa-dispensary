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
        Schema::create('investigation_requests', function (Blueprint $table) {
            $table->id();
                  $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
        $table->foreignId('investigation_id')->constrained()->cascadeOnDelete();

        $table->decimal('price', 12, 2);

        $table->enum('status', [
            'waiting_payment',
            'requested',
            'processing',
            'completed'
        ])->default('requested');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investigation_requests');
    }
};
