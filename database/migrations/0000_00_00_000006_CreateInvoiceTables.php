<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;

class CreateInvoiceTables extends Migration
{
    protected $connection = 'site';

    public function up()
    {
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->integer('customer_id')->unsigned();
                $table->integer('currency_id')->unsigned();
                $table->integer('order_id')->unsigned()->nullable();
                $table->integer('address_id')->unsigned();
                $table->integer('invoice_number');
                $table->float('total');
                $table->double('credit', 8, 2);
                $table->enum('status', [0, 1, 2, 3, 4]);
                $table->unsignedTinyInteger('suspend_count')->default(0);
                $table->timestamp('due_at')->nullable();
                $table->timestamp('last_reminder');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->foreign('customer_id')
                    ->references('id')->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->foreign('order_id')
                    ->references('id')->on('orders')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->foreign('address_id')
                    ->references('id')->on('addresses')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->foreign('currency_id')
                    ->references('id')->on(new Expression('main.currencies'))
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('invoice_id')->unsigned();
                $table->string('item');
                $table->string('product');
                $table->longText('description');
                $table->float('price');
                $table->integer('quantity');
                $table->integer('package_id')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('invoice_id')
                    ->references('id')->on('invoices')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('invoice_totals')) {
            Schema::create('invoice_totals', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('invoice_id')->unsigned();
                $table->string('item');
                $table->float('price');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('invoice_id')
                    ->references('id')->on('invoices')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('transaction_id');
                $table->integer('invoice_id')->unsigned();
                $table->integer('user_id')->unsigned();
                $table->integer('customer_id')->unsigned();
                $table->string('gateway_id');
                $table->integer('currency_id')->unsigned();
                $table->float('amount');
                $table->integer('payment_method')->unsigned();
                $table->integer('status')->unsigned();
                $table->string('message');
                $table->longText('json_response');
                $table->text('api_type')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->foreign('customer_id')
                    ->references('id')->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->foreign('invoice_id')
                    ->references('id')->on('invoices')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->foreign('currency_id')
                    ->references('id')->on(new Expression('main.currencies'))
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
    	Schema::drop('transactions');
        Schema::drop('invoice_totals');
        Schema::drop('invoice_items');
        Schema::drop('invoices');
    }

}
