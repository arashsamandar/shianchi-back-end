<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpecialConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('special_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type'); // wego_coin discount gift 

            $table->timestamp('expiration');
            $table->integer('product_id')->unsigned();
            $table->integer('upper_value');
            $table->string('upper_value_type');
            $table->char('status')->default('a'); // e = expired a = available
            $table->integer('amount');
            $table->string('text')->nullable();

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
        Schema::drop('special_conditions');
    }
}
