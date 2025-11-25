<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->date('date');
            $table->unsignedBigInteger('fund_source_id');
            $table->decimal('amount', 12, 2);
            $table->string('reference_number')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('office_id');

            $table->timestamps();

            // Foreign keys
            $table->foreign('fund_source_id')->references('id')->on('accounts')->cascadeOnUpdate();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate();
            $table->foreign('office_id')->references('id')->on('offices')->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
