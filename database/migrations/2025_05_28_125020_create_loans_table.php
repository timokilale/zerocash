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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number', 20)->unique();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('loan_type_id')->constrained('loan_types')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->integer('term_months');
            $table->decimal('monthly_payment', 12, 2);
            $table->decimal('outstanding_balance', 15, 2);
            $table->enum('status', ['pending', 'approved', 'active', 'paid', 'defaulted', 'cancelled'])->default('pending');
            $table->date('issued_date')->nullable();
            $table->date('due_date')->nullable();
            $table->boolean('auto_transferred')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
