<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExcludeApiToOrderGroupPackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_group_packages', function (Blueprint $table) {
			       $table->boolean('exclude_from_api')->default(0);
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
			        $table->dropColumn('exclude_from_api');
        });
    }
}
