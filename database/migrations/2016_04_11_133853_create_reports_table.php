<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table)
        {
            $table->increments('id')->unique();
            $table->text('body');
            $table->string('type');
            $table->integer('sender_id')->unsigned();
            $table->integer('reported_store_id')->unsigned()->nulllable();
            $table->integer('reported_product_id')->unsigned()->nullable();
            $table->char('is_read',1)->default('N'); // N = no read; R = read
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reported_store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('reported_product_id')->references('id')->on('products')->onDelete('cascade');

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
        Schema::drop('reports');
    }
}
