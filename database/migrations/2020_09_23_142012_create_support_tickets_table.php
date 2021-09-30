<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupportTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('site')->create('support_tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('assignee_by')->nullable();
            $table->string('subject');
            $table->enum('status', ['open', 'pending', 'closed', 'awaiting_replay']);
            $table->enum('priority', ['low', 'medium', 'high', 'emergency'])->nullable();
            $table->string('last_action');
            $table->timestamps();
        });

        Schema::connection('site')->create('support_ticket_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('support_ticket_id');
            $table->unsignedInteger('replay_by');
            $table->longText('message');
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
        Schema::connection('site')->dropIfExists('support_ticket_messages');
        Schema::connection('site')->dropIfExists('support_tickets');
    }
}
