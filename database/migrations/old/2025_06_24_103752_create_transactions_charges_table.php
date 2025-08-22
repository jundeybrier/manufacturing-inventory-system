<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaction_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('token', 10)->unique();
            $table->string('type');
            $table->string('name');
            $table->unsignedBigInteger('fund_source_id');
            $table->string('fund_source');
            $table->string('code')->nullable();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->decimal('conversion_rate', 10, 2)->default(0.00);
            $table->dateTime('datetime_created');
            $table->string('created_by');
            $table->integer('status')->default(1);
            $table->dateTime('datetime_voided')->nullable();
            $table->string('voided_by')->nullable();

            $table->index('fund_source_id');
            $table->index('type');
            $table->index('product_id');

            $table->foreign('product_id')->references('id')->on('transaction_details')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('fund_source_id')->references('id')->on('fund_source')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_charges');
    }
};
