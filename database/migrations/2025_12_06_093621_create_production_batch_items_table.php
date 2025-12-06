<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_batch_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('production_batch_id')
                ->constrained('production_batches')
                ->cascadeOnDelete();

            $table->foreignId('material_id')
                ->constrained('inventory_items')
                ->cascadeOnDelete();

            $table->foreignId('from_stage_id')
                ->nullable()
                ->constrained('stages')
                ->nullOnDelete();

            $table->decimal('quantity_used', 12, 4); // ACTUAL used quantity
            $table->string('unit')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_batch_items');
    }
};
