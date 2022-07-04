<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpecificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('specifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('important')->default(0);
            $table->boolean('for_buy')->default(0);
            $table->boolean('is_text_field')->default(1);
            $table->boolean('multi_value')->default(0);
            $table->boolean('searchable')->default(0);
            $table->integer('title_id')->unsigned()->nullable();
            $table->integer('category_id')->unsigned();
            $table->timestamps();
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
        Schema::drop('specifications');
    }
}
