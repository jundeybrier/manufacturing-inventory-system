<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete(); // finished product
            $table->unsignedInteger('planned_output_qty');
            $table->unsignedInteger('actual_output_qty')->nullable();
            $table->date('production_date');
            $table->enum('status', ['Open', 'Completed'])->default('Open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
