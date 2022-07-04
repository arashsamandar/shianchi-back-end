<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuyerAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buyer_addresses', function (Blueprint $table) {
            $table->increments('id');

            $table->text('address');
            $table->integer('city_id')->default(1);
            $table->integer('province_id')->default(1698);

            $table->string('latitude')->default('0.0.0');
            $table->string('longitude')->default('0.0.0');

            $table->string('postal_code');
            $table->string('phone_number');
            $table->string('prefix_phone_number');

            $table->string('mobile_number');
            $table->string('prefix_mobile_number');

            $table->string('receiver_first_name');
            $table->string('receiver_last_name');

            $table->integer('user_id')->unsigned();

            $table->foreign('user_id')->references('id')
                ->on('users')
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
        Schema::drop('buyer_addresses');
    }
}
