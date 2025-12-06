<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('production_order_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete(); // raw material
            $table->decimal('planned_qty', 12, 3)->default(0);
            $table->decimal('actual_qty', 12, 3)->default(0);
            $table->timestamps();

            $table->unique(
                ['production_order_id', 'inventory_item_id'],
                'po_material_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_materials');
    }
};
