<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorldcupPredictionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('worldcup_predictions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id');
            $table->integer('prediction');
            $table->integer('user_id')->unsigned();
            $table->string('mobile_number');
            $table->unique(['user_id','game_id']);
            $table->unique(['game_id','mobile_number']);
            $table->timestamps();
            $table->foreign('game_id')->references('id')->on('worldcup_games')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('worldcup_predictions');
    }
}
