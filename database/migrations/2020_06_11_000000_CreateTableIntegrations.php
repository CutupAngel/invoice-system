<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableIntegrations extends Migration
{
    protected $connection = 'site';

    public function up()
    {
		if (!Schema::hasTable('integrations')) {
            Schema::create('integrations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('hostname');
                $table->enum('https', [0, 1]);
                $table->text('port');
                $table->string('username');
                $table->string('password');
                $table->string('nameserver_1');
                $table->string('nameserver_2');
                $table->string('nameserver_3');
                $table->string('nameserver_4');
                $table->string('nameserver_ip_1');
                $table->string('nameserver_ip_2');
                $table->string('nameserver_ip_3');
                $table->string('nameserver_ip_4');
                $table->integer('qty')->unsigned();
                $table->string('server_group_selected'); // eg: reseller | whatever that in server_group field
                $table->string('server_group_available'); // eg: reseller | shared
                $table->string('integration_type'); // eg: directadmin | cpanel
                $table->timestamps();
                $table->softDeletes();

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
        Schema::drop('integrations');
    }
}
