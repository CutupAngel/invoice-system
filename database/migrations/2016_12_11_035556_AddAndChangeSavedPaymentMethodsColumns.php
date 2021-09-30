<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAndChangeSavedPaymentMethodsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('saved_payment_methods', function (Blueprint $table) {
			$table->dropColumn('custom1');
			$table->dropColumn('custom2');
			$table->dropColumn('custom3');
			$table->string('last4')->nullable();
			$table->string('expiration_month')->nullable();
			$table->string('expiration_date')->nullable();
			$table->string('token')->nullable();
			$table->string('default')->nullable();
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
			 $table->dropColumn('last4');
			 $table->dropColumn('expiration_month');
			 $table->dropColumn('expiration_date');
			 $table->dropColumn('token');
			 $table->dropColumn('default');
			 $table->string('custom1')->nullable();
			 $table->string('custom2')->nullable();
			 $table->string('custom3')->nullable();
        });
    }
}
