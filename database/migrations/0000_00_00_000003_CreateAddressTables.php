<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;

class CreateAddressTables extends Migration
{
    protected $connection = 'site';

    public function up()
    {
        if (!Schema::hasTable('addresses')) {
            Schema::create('addresses', function (Blueprint $table) {
                $table->increments('id');
                $table->text('contact_name');
                $table->string('business_name');
                $table->text('phone');
                $table->text('fax');
                $table->text('email');
                $table->text('website');
                $table->text('address_1');
                $table->text('address_2');
                $table->text('address_3');
                $table->text('address_4');
                $table->text('city');
                $table->integer('county_id')->nullable();
                $table->text('postal_code');
                $table->integer('country_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('user_contacts')) {
            Schema::create('user_contacts', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->integer('address_id')->unsigned();
                $table->enum('type', [0, 1, 2, 3, 4]);
                $table->timestamps();

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->foreign('address_id')
                    ->references('id')->on('addresses')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
    	Schema::drop('user_contacts');
        Schema::drop('addresses');
    }

}