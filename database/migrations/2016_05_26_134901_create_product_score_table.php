<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductScoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_score', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('score_title_id')->unsigned();
            $table->integer('score');
            $table->integer('product_id')->unsigned();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->foreign('score_title_id')->references('id')->on('score_title')->onDelete('cascade');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['user_id','product_id','score_title_id']);
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
        Schema::drop('product_score');
    }
}
