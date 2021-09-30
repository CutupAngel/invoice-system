<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;

class CreateOrderTables extends Migration
{
    protected $connection = 'site';

    public function up()
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->integer('customer_id')->unsigned();
                $table->integer('package_id')->unsigned();
                $table->integer('cycle_id')->unsigned();
                $table->enum('status', [0, 1, 2, 3, 4, 5, 6, 7]);
                $table->timestamp('last_invoice');
                $table->float('price');
                $table->integer('currency_id')->unsigned();
                $table->string('integration');
                $table->integer('trial_order')->nullable();
                $table->date('trial_expire_date')->nullable();
                $table->time('trial_expire_time')->nullable();
                $table->string('fraudlabs_status');
                $table->longText('fraudlabs_json');
                $table->text('api_type')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->foreign('customer_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->foreign('package_id')
                    ->references('id')->on('order_group_packages')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->foreign('cycle_id')
                    ->references('id')->on('order_group_package_cycles')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }

        if (!Schema::hasTable('order_settings')) {
            Schema::create('order_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('order_id')->unsigned();
                $table->string('setting_name')->index();
                $table->string('setting_value');
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['order_id', 'setting_name']);

                $table->foreign('order_id')
                    ->references('id')->on('orders')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }
    }

    public function down()
    {
    	Schema::drop('order_settings');
        Schema::drop('orders');
    }

}
