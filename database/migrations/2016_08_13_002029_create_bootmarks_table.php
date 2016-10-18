<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBootmarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bootmarks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('media_id')->unsigned()->nullable();
            $table->foreign('media_id')->references('id')->on('media');
            $table->integer('link_id')->unsigned()->nullable();
            $table->foreign('link_id')->references('id')->on('links');
            $table->string('type');
            $table->string('location');
            $table->integer('karma');
            $table->longText('description')->nullable();
            $table->double('lat');
            $table->double('lng');
            $table->tinyInteger('remote');
            $table->tinyInteger('discoverable');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bootmarks');
    }
}
