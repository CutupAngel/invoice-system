<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;

class CreateOrderGroupTables extends Migration
{
    protected $connection = 'site';

    public function up()
    {
        if (! Schema::hasTable('order_groups')) {
            Schema::create('order_groups', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->index();
                $table->string('name');
                $table->longText('description');
                $table->string('url', 100)->index();
                $table->integer('type');
                $table->string('api_type')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }


        if (! Schema::hasTable('order_group_packages')) {
            Schema::create('order_group_packages', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('group_id')->unsigned()->index();
                $table->string('name');
                $table->longText('description');
                $table->boolean('tax');
                $table->boolean('prorate');
                $table->integer('trial');
                $table->integer('theme')->default(0);
                $table->integer('type');
                $table->string('api_type')->nullable();
                $table->string('url');
                $table->string('integration');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('group_id')
                    ->references('id')->on('order_groups')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }


        if (! Schema::hasTable('order_group_package_cycles')) {
            Schema::create('order_group_package_cycles', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('package_id')->unsigned()->index();
                $table->float('price');
                $table->float('fee');
                $table->integer('cycle');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('package_id')
                    ->references('id')->on('order_group_packages')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }


        if (! Schema::hasTable('order_group_package_files')) {
            Schema::create('order_group_package_files', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('package_id')->unsigned()->index();
                $table->string('filename');
                $table->text('path');
                $table->text('mime');
                $table->integer('size');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('package_id')
                    ->references('id')->on('order_group_packages')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }

        if (! Schema::hasTable('order_group_package_images')) {
            Schema::create('order_group_package_images', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('package_id')->unsigned()->index();
                $table->string('filename');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('package_id')
                    ->references('id')->on('order_group_packages')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }

    }

    public function down()
    {
        Schema::drop('order_group_package_files');
        Schema::drop('order_group_package_cycles');
        Schema::drop('order_group_packages');
        Schema::drop('order_groups');
    }

}
