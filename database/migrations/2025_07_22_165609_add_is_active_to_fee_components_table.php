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
            $table->boolean('is_active')->default(1)->after('currency');
        });
    }

    public function down()
    {
        Schema::table('fee_components', function ($table) {
            $table->dropColumn('is_active');
        });
    }

};
