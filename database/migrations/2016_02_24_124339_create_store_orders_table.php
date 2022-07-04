<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_store', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('store_id')->unsigned();
            $table->integer('order_id')->unsigned();
            $table->integer('total_discount');
            $table->integer('total_delivery_price')->nullable();
            $table->integer('total_product_price');
            $table->integer('payment_type')->nullable();
            $table->string('payment_code')->nullable();

            $table->integer('quantity');

//            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
  //          $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
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
        Schema::drop('order_store');
    }
}
