<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameCardExpDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('saved_payment_methods', function (Blueprint $table) {
			$table->dropColumn('expiration_date');
			$table->string('expiration_year')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('saved_payment_methods', function (Blueprint $table) {
			 $table->string('expiration_date')->nullable();
			 $table->dropColumn('expiration_year');
        });
    }
}
