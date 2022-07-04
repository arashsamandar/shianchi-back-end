<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFreeShippingConditionStore extends Migration
{
    /**
     * Run the migrations.
     * age kol bud city = #
     * @return void
     */
    public function up()
    {
        Schema::create('free_shipping_condition_store', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('store_id')->unsigned();

            $table->integer('free_shipping_condition_id')->unsigned();

            $table->integer('upper_value');
            $table->string('city');
            $table->integer('city_id')->default(1);


            $table->foreign('store_id')
                ->references('id')
                ->on('stores')
                ->onDelete('cascade');

            $table->foreign('free_shipping_condition_id')
                ->references('id')
                ->on('free_shipping_conditions')
                ->onDelete('cascade');

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
        Schema::drop('free_shipping_condition_store');
    }
}
