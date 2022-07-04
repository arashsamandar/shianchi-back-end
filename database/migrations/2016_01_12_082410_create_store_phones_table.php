<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStorePhonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_phones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('prefix_phone_number',4);
            $table->smallInteger('type');
            $table->string('phone_number',8);
            $table->integer('store_id')->unsigned();
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
        Schema::drop('store_phones');
    }
}
