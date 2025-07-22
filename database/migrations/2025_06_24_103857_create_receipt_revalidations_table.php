<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('revalidations', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id');
            $table->dateTime('datetime_revalidated');
            $table->string('revalidated_by');
            $table->text('remarks')->nullable();

            $table->foreign('transaction_id')->references('id')->on('transactions')->onUpdate('restrict')->onDelete('restrict');
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revalidations');
    }
};
