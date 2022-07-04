<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->integer('store_id')->unsigned();
            $table->integer('value_id')->unsigned()->nullable();
            $table->integer('color_id')->unsigned()->nullable();
            $table->integer('warranty_id')->unsigned()->nullable();
            $table->integer('current_price');
            $table->integer('quantity');
            $table->integer('order')->nullable();
            $table->unique(['product_id','store_id','value_id','color_id','warranty_id'],'u_id');
            $table->foreign('store_id')
                ->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('value_id')
                ->references('id')->on('values');
            $table->foreign('color_id')
                ->references('id')->on('colors');
            $table->foreign('warranty_id')
                ->references('id')->on('warranties');
            $table->foreign('product_id')
                ->references('id')->on('products')->onDelete('cascade');
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
        Schema::dropIfExists('product_details');
    }
}
