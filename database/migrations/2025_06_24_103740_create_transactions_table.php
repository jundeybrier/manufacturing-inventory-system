<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('token', 10);
            $table->date('date');
            $table->string('reference_number')->nullable()->unique();
            $table->integer('or_number')->nullable()->unique();
            $table->string('lastname');
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('rep_name')->nullable();
            $table->double('amount_paid', 10, 2);
            $table->string('remarks')->nullable();
            $table->string('counter', 100)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->dateTime('datetime_created')->nullable();
            $table->string('created_by');
            $table->dateTime('datetime_voided')->nullable();
            $table->string('voided_by')->nullable();
            $table->dateTime('datetime_validated')->nullable();
            $table->string('validated_by')->nullable();

            $table->index('datetime_created');
            $table->index('validated_by');
            $table->index('datetime_validated');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
