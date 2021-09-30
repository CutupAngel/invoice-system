<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaxToItemsAndTotals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
			$table->float('tax')->nullable();
        });
        Schema::table('invoice_totals', function (Blueprint $table) {
			$table->float('tax')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('invoice_items', function (Blueprint $table) {
			 $table->dropColumn('tax');
        });
         Schema::table('invoice_totals', function (Blueprint $table) {
			 $table->dropColumn('tax');
        });
    }
}
