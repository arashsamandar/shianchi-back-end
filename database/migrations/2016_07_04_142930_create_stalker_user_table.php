<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateStalkerUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stalker_user',function(Blueprint $table){
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->integer('product_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::table('stalker_user',function(Blueprint $table){
            $table->dropForeign('stalker_user_user_id_foreign');
            $table->dropForeign('stalker_user_product_id_foreign');
        });
        Schema::drop('stalker_user');
    }
}
