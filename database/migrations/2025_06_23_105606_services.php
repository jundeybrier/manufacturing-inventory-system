<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('token', 10)->unique();
            $table->string('type'); // references service_types.name
            $table->string('name')->unique();
            $table->unsignedBigInteger('fund_source_id');
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->dateTime('datetime_created');
            $table->string('created_by');
            $table->integer('status')->default(1);
            $table->dateTime('datetime_disabled')->nullable();
            $table->string('disabled_by')->nullable();

            // Foreign Keys
            $table->foreign('fund_source_id')
                ->references('id')->on('fund_source')
                ->onUpdate('restrict')->onDelete('restrict');

            $table->foreign('type')
                ->references('name')->on('services_type')
                ->onUpdate('restrict')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
