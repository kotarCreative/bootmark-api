<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveDefaultSpamReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports', function($table) {
            $table->dropColumn('reason');
        });

        Schema::table('reports', function($table) {
            $table->enum('reason', ['spam', 'inappropriate']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports', function ($table) {
            $table->dropColumn('reason');
        });

        Schema::table('reports', function($table) {
            $table->enum('reason', ['spam', 'inappropriate'])->default('spam');
        });
    }
}
