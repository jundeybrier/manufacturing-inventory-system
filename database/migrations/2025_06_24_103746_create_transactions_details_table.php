<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('t_id')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('service_id');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->decimal('conversion_rate', 10, 2)->default(0.00);
            $table->date('date');
            $table->dateTime('datetime_created');
            $table->string('created_by');
            $table->integer('user_id');

            $table->index('t_id');
            $table->index('user_id');
            $table->index('service_id');

            $table->foreign('t_id')->references('id')->on('transactions')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('service_id')->references('id')->on('products')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
