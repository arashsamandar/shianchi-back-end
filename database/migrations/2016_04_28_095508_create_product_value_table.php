<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductValueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_value',function(Blueprint $table){
            $table->increments('id');

            $table->integer('product_id')->unsigned();
            $table->integer('value_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('value_id')->references('id')->on('values')->onDelete('cascade');
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
        Schema::table('product_value', function (Blueprint $table) {
            $table->dropForeign('product_value_product_id_foreign');
            $table->dropForeign('product_value_value_id_foreign');
        });
        Schema::drop('product_value');
    }
}


