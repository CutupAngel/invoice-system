<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;

class CreateJobTables extends Migration
{
    protected $connection = 'site';

    public function up()
    {
        if (!Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('queue');
                $table->longText('payload');
                $table->tinyInteger('attempts')->unsigned();
                $table->tinyInteger('reserved')->unsigned();
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
                $table->index(['queue', 'reserved', 'reserved_at']);
            });
        }

        if (!Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->increments('id');
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->timestamp('failed_at')->useCurrent();
            });
        }
    }

    public function down()
    {
    	Schema::drop('failed_jobs');
        Schema::drop('jobs');
    }

}