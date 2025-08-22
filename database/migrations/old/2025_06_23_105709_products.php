<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('token', 10)->unique();
            $table->string('type');
            $table->string('name')->unique();
            $table->dateTime('datetime_created');
            $table->string('created_by');
            $table->integer('status')->default(1);
            $table->dateTime('datetime_disabled')->nullable();
            $table->string('disabled_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products_services');
        Schema::dropIfExists('products');
    }
};
