<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoiceBuilderDataToInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
			$table->float('tax')->nullable();
        });
        Schema::table('invoice_items', function (Blueprint $table) {
			$table->integer('tax_class')->nullable();
        });
        Schema::table('invoice_totals', function (Blueprint $table) {
			$table->integer('tax_class')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('invoices', function (Blueprint $table) {
			 $table->dropColumn('tax');
        });
         Schema::table('invoice_items', function (Blueprint $table) {
			 $table->dropColumn('tax_class');
        });
         Schema::table('invoice_totals', function (Blueprint $table) {
			 $table->dropColumn('tax_class');
        });
    }
}
