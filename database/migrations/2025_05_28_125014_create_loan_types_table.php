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
        Schema::create('loan_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 10)->unique();
            $table->text('description')->nullable();
            $table->decimal('base_interest_rate', 5, 2);
            $table->decimal('max_amount', 15, 2);
            $table->decimal('min_amount', 15, 2);
            $table->integer('max_term_months');
            $table->integer('min_term_months');
            $table->decimal('processing_fee_rate', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_types');
    }
};
