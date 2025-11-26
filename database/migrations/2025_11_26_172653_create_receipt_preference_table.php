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
        Schema::create('receipt_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Receipt Type: Form 51 Continuous or Booklet
            $table->enum('receipt_type', ['continuous_form51', 'booklet_form51'])
                ->default('continuous_form51');

            // Layout components (X/Y/Font/Spacing)
            $table->json('office_name')->nullable();
            $table->json('date')->nullable();
            $table->json('payor_info')->nullable();
            $table->json('particulars')->nullable();
            $table->json('total')->nullable();
            $table->json('amount_words')->nullable();
            $table->json('cashier_name')->nullable();

            // Page size per type
            $table->string('page_width')->default('90mm');
            $table->string('page_height')->default('188mm');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_preference');
    }
};
