<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuaranteeStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guarantee_store', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('store_id')->unsigned();

            $table->integer('expiration_time');
            $table->integer('guarantee_id')->unsigned();

            $table->foreign('guarantee_id')->references('id')->on('guarantees');

            $table->foreign('store_id')->references('id')->on('stores');

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
        Schema::drop('guarantee_store');
    }
}
