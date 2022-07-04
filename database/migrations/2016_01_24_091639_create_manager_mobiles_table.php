<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManagerMobilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manager_mobiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('prefix_phone_number');
            $table->string('phone_number');
            $table->integer('store_id')->unsigned();
//            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::table('manager_mobiles', function(Blueprint $table) {
//            $table->dropForeign('manager_mobiles_store_id_foreign');
//        });
        Schema::drop('manager_mobiles');
    }
}
