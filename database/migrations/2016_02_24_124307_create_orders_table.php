<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //TODO: ADD PROGRESSABLE
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('status');
            $table->integer('user_id')->unsigned();
            $table->char('payment_status',5)->default("n");///p = purchased and n = not purchased
            $table->dateTime('time');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::drop('orders');
    }
}
