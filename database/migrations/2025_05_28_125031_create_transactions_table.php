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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 20)->unique();
            $table->foreignId('sender_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('receiver_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('transaction_type_id')->constrained('transaction_types')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->decimal('fee_amount', 8, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('reference', 100)->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['transaction_number']);
            $table->index(['status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
