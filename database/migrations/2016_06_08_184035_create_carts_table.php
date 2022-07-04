<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->integer('quantity');
            $table->integer('color_id')->unsigned();
            $table->integer('user_id')->unsigned();

            $table->integer('value_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->foreign('color_id')->references('id')->on('colors')->onDelete('cascade');

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
        Schema::drop('carts');
    }
}
