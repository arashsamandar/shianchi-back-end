<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryTitleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_title', function (Blueprint $table) {
            $table->integer('category_id')->unsigned();
            $table->integer('title_id')->unsigned();
            $table->unique(['category_id', 'title_id']);
            $table->timestamps();
            $table->foreign('title_id')->references('id')->on('titles')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('category_title');
    }
}
