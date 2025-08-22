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
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // For cross-office/cloud sync

            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('fee_component_id')->nullable()->constrained('fee_components')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();

            $table->string('description')->nullable(); // For custom/manual items
            $table->integer('quantity')->default(1);
            $table->decimal('amount', 12, 2);
            $table->decimal('total', 12, 2);

            $table->string('currency')->default('PHP');
            $table->decimal('exchange_rate', 10, 4)->nullable();

            $table->timestamp('synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
