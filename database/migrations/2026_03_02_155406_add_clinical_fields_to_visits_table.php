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
        Schema::table('visits', function (Blueprint $table) {
        $table->text('chief_complaint')->nullable();
        $table->text('past_medical_history')->nullable();
        $table->text('family_history')->nullable();
        $table->text('social_history')->nullable();
        $table->text('rvs')->nullable();
        $table->text('examination')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            //
        });
    }
};
