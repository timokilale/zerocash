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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->foreignId('withdrawn_by')->constrained('users')->onDelete('cascade');
            $table->enum('withdrawal_method', ['cash', 'check', 'atm', 'online']);
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('authorized_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('authorized_at')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'status']);
            $table->index(['withdrawn_by']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
