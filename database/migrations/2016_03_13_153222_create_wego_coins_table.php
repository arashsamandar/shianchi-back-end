<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWegoCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wego_coins', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('expiration');
            $table->integer('user_id')->unsigned();
            $table->integer('store_id')->unsigned();

            $table->integer('amount');
            $table->char('status')->default('a'); // e = expired a = available

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');


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
        Schema::drop('wego_coins');
    }
}
