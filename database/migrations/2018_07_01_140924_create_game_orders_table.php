<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGameOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('mobile_number');
            $table->string('email');
            $table->string('type');
            $table->dateTime('game_date');
            $table->integer('payment_type');
            $table->string('status')->default(\App\GameOrder::NOT_PAYED);
            $table->integer('price');
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
        Schema::dropIfExists('game_orders');
    }
}
