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
            $table->uuid('uuid')->unique(); // For cross-office/cloud sync

            $table->string('or_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->foreignId('office_id')->constrained('offices')->onDelete('restrict');
            $table->foreignId('session_id')->nullable()->constrained('cashier_sessions')->nullOnDelete();

            // Customer details
            $table->string('firstname')->nullable();
            $table->string('middlename')->nullable();
            $table->string('lastname')->nullable();
            $table->string('rep_name')->nullable();
            $table->string('customer_name')->nullable();

            $table->text('remarks')->nullable();
            $table->decimal('total_amount', 12, 2);

            $table->string('currency')->default('PHP');
            $table->decimal('exchange_rate', 10, 4)->nullable();

            $table->enum('status', ['completed', 'voided'])->default('completed');
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();

            $table->timestamp('synced_at')->nullable();

            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
