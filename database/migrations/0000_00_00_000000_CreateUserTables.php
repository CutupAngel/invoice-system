<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;

class CreateUserTables extends Migration
{
    protected $connection = 'site';

    public function up()
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('username')->unique();
                $table->string('email')->unique();
                $table->string('password');
                $table->smallInteger('account_type')->default(1);
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
                $table->tinyInteger('authEnabled');
                $table->string('authSecret');
                $table->timestamp('last_login');
                $table->string('stripeId');
                $table->string('vat_number');
                $table->string('fraudlabs_status');
                $table->longText('fraudlabs_json');
                $table->text('sandbox_api_key');
                $table->text('live_api_key');
                $table->text('api_type')->nullable();

                $table->unique(['username', 'password']); // This index will speed up logins as it'll cache the usernames and passwords.
            });
        }

        if (!Schema::hasTable('user_link')) {
            Schema::create('user_link', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->index();
                $table->integer('parent_id')->unsigned()->index();

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->foreign('parent_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('restrict')
                    ->onUpdate('cascade');
            });
        }

        if (!Schema::hasTable('password_resets')) {
            Schema::create('password_resets', function (Blueprint $table) {
                $table->string('username')->index();
                $table->string('email')->index();
                $table->string('token')->index();
                $table->timestamp('created_at');
            });
        }

        if (!Schema::hasTable('user_settings')) {
            Schema::create('user_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('name');
                $table->text('value');
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['user_id', 'name']);

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }

    }

    public function down()
    {
    	Schema::drop('user_settings');
        Schema::drop('password_resets');
        Schema::drop('user_link');
        Schema::drop('users');
    }

}
