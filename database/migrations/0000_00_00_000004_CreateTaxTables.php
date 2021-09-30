<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;

class CreateTaxTables extends Migration
{
    protected $connection = 'site';

    public function up()
    {
        if (!Schema::hasTable('taxZones')) {
            Schema::create('taxZones', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->integer('user_id')->unsigned();
                $table->string('api_type')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('taxZoneCounties')) {
            Schema::create('taxZoneCounties', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('zone_id')->unsigned();
                $table->integer('county_id')->unsigned();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('county_id')
                    ->references('id')->on(new Expression('main.counties'))
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->foreign('zone_id')
                    ->references('id')->on('taxZones')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('taxClasses')) {
            Schema::create('taxClasses', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->integer('user_id')->unsigned();
                $table->string('api_type')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('taxRates')) {
            Schema::create('taxRates', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('zone_id')->unsigned();
                $table->integer('class_id')->unsigned();
                $table->float('rate');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('zone_id')
                    ->references('id')->on('taxZones')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->foreign('class_id')
                    ->references('id')->on('taxClasses')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
    	Schema::drop('taxRates');
        Schema::drop('taxClasses');
        Schema::drop('taxZoneCounties');
        Schema::drop('taxZones');
    }

}
