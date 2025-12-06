<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable(); // SKU or internal code
            $table->string('category'); // veneer, pre_fab, plywood, raw_material
            $table->string('unit')->default('pcs'); // pcs, w/s, pairs, sheets, kg, etc.
            $table->boolean('is_producible')->default(false);
            $table->boolean('is_repairable')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
