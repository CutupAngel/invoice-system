<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImagesToOrderGroupPackagesImages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (!Schema::hasTable('order_group_package_images')) {
          Schema::create('order_group_package_images', function (Blueprint $table) {
			       $table->integer('id');
             $table->integer('package_id');
             $table->string('filename');
             $table->string('path');
             $table->timestamp('created_at')->nullable();
             $table->timestamp('updated_at')->nullable();
             $table->timestamp('deleted_at')->nullable();
        });
    }
  }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::drop('order_group_package_images');
    }
}
