<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_item_id')
                ->constrained()
                ->cascadeOnDelete(); // finished product (plywood)

            $table->foreignId('material_id')
                ->constrained('inventory_items')
                ->cascadeOnDelete(); // raw material (veneer, core, glue)

            $table->decimal('quantity', 12, 4); // standard qty needed per sheet
            $table->string('unit')->nullable(); // optional

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_of_materials');
    }
};

