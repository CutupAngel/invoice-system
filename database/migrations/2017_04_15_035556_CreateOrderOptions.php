<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderOptions extends Migration
{
    protected $connection = 'site';

    public function up()
    {
		if (!Schema::hasTable('order_options')) {
            Schema::create('order_options', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('order_id')->unsigned()->index();
                $table->integer('option_value_id')->unsigned()->index();
                $table->float('amount');
                $table->string('value', 255);
                $table->integer('cycle_type');
                $table->integer('status');
                $table->timestamp('last_invoice');
                $table->timestamps();
                $table->softDeletes();

				$table->foreign('order_id')
                    ->references('id')
                    ->on('orders')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

        $table->foreign('option_value_id')
                    ->references('id')
                    ->on('option_values')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }
    }

    public function down()
    {
        Schema::drop('order_options');
    }
}
