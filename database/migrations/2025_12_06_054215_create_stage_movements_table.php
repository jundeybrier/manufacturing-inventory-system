<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stage_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();

            $table->foreignId('from_stage_id')->nullable()->constrained('stages')->nullOnDelete();
            $table->foreignId('to_stage_id')->nullable()->constrained('stages')->nullOnDelete();

            $table->decimal('quantity', 12, 3);
            $table->date('movement_date');
            $table->string('remarks')->nullable();

            $table->foreignId('production_order_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->index(['inventory_item_id', 'movement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_movements');
    }
};
