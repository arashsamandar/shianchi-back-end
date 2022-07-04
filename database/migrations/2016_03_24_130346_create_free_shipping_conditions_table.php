<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFreeShippingConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * type = 1 ersal e rayegan be kole keshvar balaye 0 toman
     * type = 2 ersal e rayegan be kole keshvar balaye x toman
     * type = 3 ersal e rayegan be shahre  balaye 0 toman
     * typte = 4 ersale rayegan be shahre khodam balaye x toman
     */
    public function up()
    {
        Schema::create('free_shipping_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');

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
        Schema::drop('free_shipping_conditions');
    }
}
