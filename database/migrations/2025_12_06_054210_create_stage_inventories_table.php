<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stage_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained()->cascadeOnDelete();
            $table->decimal('current_qty', 12, 3)->default(0);
            $table->timestamps();

            $table->unique(['inventory_item_id', 'stage_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_inventories');
    }
};
