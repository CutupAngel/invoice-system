<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;

class CreatePackageOptionTables extends Migration
{
    protected $connection = 'site';

    public function up()
    {
        if (!Schema::hasTable('options')) {
            Schema::create('options', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('internal_name');
                $table->string('display_name');
                $table->string('api_type')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('option_values')) {
            Schema::create('option_values', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('option_id')->unsigned();
                $table->string('display_name');
                $table->float('price');
                $table->float('fee');
                $table->integer('cycle_type')->unsigned();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('option_id')
                    ->references('id')->on('options')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('options_to_packages')) {
            Schema::create('options_to_packages', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('option_id')->unsigned();
                $table->integer('package_id')->unsigned();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('option_id')
                    ->references('id')->on('options')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->foreign('package_id')
                    ->references('id')->on('order_group_packages')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('order_group_package_settings')) {
            Schema::create('order_group_package_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('package_id')->unsigned();
                $table->string('name');
                $table->string('value');
                $table->timestamps();

                $table->foreign('package_id')
                    ->references('id')->on('order_group_packages')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::drop('order_package_settings');
    	Schema::drop('options_to_packages');
        Schema::drop('options');
        Schema::drop('option_values');
    }

}
