<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReportSpamInappropriate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports', function($table) {
            $table->enum('reason', ['spam', 'inappropriate']);
            $table->string('message')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports', function($table) {
            DB::statement('ALTER TABLE reports DROP COLUMN reason');
            $table->string('message')->change();
        });
    }
}
