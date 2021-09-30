<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationCpanels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('site')->create('integration_cpanels', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('name');
            $table->string('username');
            $table->string('hostname');
            $table->unsignedInteger('port');
            $table->text('access_key');
            $table->boolean('https')->default(0);
            $table->string('nameserver_1');
            $table->string('nameserver_2');
            $table->string('nameserver_3');
            $table->string('nameserver_4');
            $table->string('nameserver_ip_1');
            $table->string('nameserver_ip_2');
            $table->string('nameserver_ip_3');
            $table->string('nameserver_ip_4');
            $table->integer('qty')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('site')->dropIfExists('integration_cpanels');
    }
}
