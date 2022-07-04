<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_offers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_product_id')->unsigned();
            $table->integer('store_price');
            $table->integer('store_id')->unsigned();
            $table->timestamps();
            $table->foreign('order_product_id')->references('id')
                ->on('order_product')->onDelete('cascade');
            $table->foreign('store_id')->references('id')
                ->on('stores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_offers');
    }
}
