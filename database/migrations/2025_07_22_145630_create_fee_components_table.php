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
        Schema::create('fee_components', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('office_id')->nullable()->constrained('offices')->onDelete('cascade');
            $table->string('name');
            $table->decimal('base_amount', 10, 2)->nullable();
            $table->boolean('is_variable')->default(false);
            $table->string('currency')->default('PHP');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_components');
    }
};
