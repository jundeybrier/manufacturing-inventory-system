<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_outputs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('production_batch_id')
                ->constrained('production_batches')
                ->cascadeOnDelete();

            $table->decimal('quantity', 12, 4)
                ->comment('Actual number of sheets produced');

            $table->foreignId('to_stage_id')
                ->nullable()
                ->constrained('stages')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_outputs');
    }
};
