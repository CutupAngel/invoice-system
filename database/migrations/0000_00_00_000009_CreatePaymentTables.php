<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;

class CreatePaymentTables extends Migration
{
    protected $connection = 'site';

    public function up()
    {
        if (!Schema::hasTable('saved_payment_methods')) {
            Schema::create('saved_payment_methods', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('gateway_id');
                $table->integer('type')->unsigned();
                $table->integer('billing_address_id')->unsigned();
                $table->text('custom1');
                $table->text('custom2');
                $table->text('custom3');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->foreign('billing_address_id')
                    ->references('id')->on('addresses')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('discounts')) {
            Schema::create('discounts', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('value');
                $table->float('discount');
                $table->date('start');
                $table->date('end');
                $table->enum('type', [0, 1]);
                $table->text('api_type')->nullable();
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
    	Schema::drop('discounts');
        Schema::drop('saved_payment_methods');
    }

}
