<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stage_categories', function (Blueprint $table) {
            $table->id(); // Will match constants (1,2,3,...)
            $table->string('name');
        });

        // Add category_id to stages table
        Schema::table('stages', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('stage_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::dropIfExists('stage_categories');
    }
};
