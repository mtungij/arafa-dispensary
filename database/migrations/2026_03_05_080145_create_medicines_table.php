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
    Schema::create('medicines', function (Blueprint $table) {

        $table->id();

        $table->foreignId('company_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('name');

        $table->string('category')->nullable(); 
        // tablet, syrup, injection

        $table->integer('quantity')->default(0);

        $table->decimal('buy_price', 12, 2);

        $table->decimal('sell_price_cash', 12, 2)->nullable();

        $table->decimal('sell_price_insurance', 12, 2)->nullable();

        $table->date('expire_date')->nullable();

        $table->enum('type', ['insurance','private']);

        $table->timestamps();

        // Prevent duplicate medicines
        $table->unique(['company_id','name','type']);

        // Faster search
        $table->index(['company_id','name']);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
