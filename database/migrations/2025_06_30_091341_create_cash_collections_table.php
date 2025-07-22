<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cash_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashiering_session_id')->constrained()->onDelete('cascade');
            $table->integer('denomination'); // e.g. 1000, 500, etc.
            $table->integer('quantity'); // how many of that bill
            $table->decimal('total_amount', 12, 2); // computed value
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_collections');
    }
};
