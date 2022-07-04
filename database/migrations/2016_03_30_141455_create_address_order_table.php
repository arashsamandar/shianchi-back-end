<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address_order', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('buyer_address_id')->unsigned();
            $table->integer('order_id')->unsigned();

            $table->foreign('buyer_address_id')->references('id')->on('buyer_addresses')->onDelete('cascade');

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
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
        Schema::drop('address_order');
    }
}
