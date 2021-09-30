<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCardTypeSavedPaymentMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('saved_payment_methods', function (Blueprint $table) {
			$table->string('card_type')->nullable();
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
			 $table->dropColumn('card_type');
        });
    }
}
