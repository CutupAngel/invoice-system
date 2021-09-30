<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;

class CreateLogTables extends Migration
{
    protected $connection = 'site';

    public function up()
    {
        if (!Schema::hasTable('login_history')) {
            Schema::create('login_history', function (Blueprint $table) {
                $table->increments('id');
                $table->boolean('failed');
                $table->string('username');
                $table->string('ip');
                $table->timestamp('logout')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('activity_log')) {
            Schema::create('activity_log', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('login_id')->unsigned();
                $table->string('route');
                $table->timestamps();

                $table->foreign('login_id')->references('id')->on('login_history');
            });
        }
    }

    public function down()
    {
    	Schema::drop('activity_log');
        Schema::drop('login_history');
    }

}