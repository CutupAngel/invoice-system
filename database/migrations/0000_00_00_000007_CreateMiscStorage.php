<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;

class CreateMiscStorage extends Migration
{
    protected $connection = 'site';

    public function up()
    {
        if (!Schema::hasTable('misc_storage')) {
            Schema::create('misc_storage', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('value');
                $table->integer('user_id')->unsigned();
                $table->timestamps();

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
    	Schema::drop('misc_storage');
    }

}