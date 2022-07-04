<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGameScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_scores', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('telegram_username')->nullable();
            $table->string('email')->nullable();
            $table->string('call_number')->nullable();
            $table->integer('score');
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
        Schema::dropIfExists('game_scores');
    }
}
