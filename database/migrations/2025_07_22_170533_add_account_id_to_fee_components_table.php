<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('fee_components', function ($table) {
            $table->foreignId('account_id')
                ->nullable()
                ->after('service_id')
                ->constrained('accounts')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('fee_components', function ($table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
