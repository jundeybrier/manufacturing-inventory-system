<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('boms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finished_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('raw_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->decimal('required_qty_per_unit', 12, 3);
            $table->timestamps();

            $table->unique(['finished_item_id', 'raw_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boms');
    }
};
