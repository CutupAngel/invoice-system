<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOutOfStockToOrderGroupPackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_group_packages', function (Blueprint $table) {
			       $table->boolean('is_outofstock')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('order_group_packages', function (Blueprint $table) {
			        $table->dropColumn('is_outofstock');
        });
    }
}
