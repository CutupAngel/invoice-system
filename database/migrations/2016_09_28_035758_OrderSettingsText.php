<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OrderSettingsText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_settings', function (Blueprint $table) {
            $table->text('setting_value')->change();
        });

        Schema::table('order_group_package_settings', function (Blueprint $table) {
            $table->text('value')->change();
        });

        Schema::table('user_settings', function (Blueprint $table) {
            $table->text('value')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_settings', function (Blueprint $table) {
            $table->string('setting_value')->change();
        });

        Schema::table('order_group_package_settings', function (Blueprint $table) {
            $table->string('value')->change();
        });

        Schema::table('user_settings', function (Blueprint $table) {
            $table->string('value')->change();
        });
    }
}
