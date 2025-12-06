<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_batches', function (Blueprint $table) {
            $table->id();
            $table->date('batch_date')->index();

            $table->foreignId('inventory_item_id')
                ->constrained()
                ->cascadeOnDelete(); // plywood product

            $table->integer('planned_quantity')->default(0);
            $table->integer('actual_output')->default(0)
                ->comment('Auto-calculated from production_outputs');

            $table->string('status')->default('Open');
            // Open, Processing, Completed

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_batches');
    }
};
