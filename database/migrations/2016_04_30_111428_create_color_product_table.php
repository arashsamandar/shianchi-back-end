<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateColorProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('color_product', function (Blueprint $table) {

            $table->integer('color_id')->unsigned();

            $table->integer('product_id')->unsigned();

            $table->foreign('color_id')->references('id')->on('colors')->onDelete('cascade');

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
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
        Schema::drop('color_product');

    }
}
