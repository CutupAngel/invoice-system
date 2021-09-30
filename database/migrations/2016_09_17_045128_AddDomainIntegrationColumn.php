<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDomainIntegrationColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_group_packages', function (Blueprint $table) {
            $table->boolean('domainIntegration');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('domainIntegration');
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
            $table->dropColumn('domainIntegration');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('domainIntegration');
        });
    }
}
