<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->string('english_name');

            $table->text('address')->nullable();
            $table->string('province_id')->default(1698);
            $table->string('city')->default('تهران');
            $table->integer('city_id')->default(1);
            $table->string('business_license')->nullable();
            $table->integer('bazaar')->nullable();
            $table->string('url');
            $table->string('lat');
            $table->string('long');

            $table->integer('wego_expiration');
            $table->text('information')->nullable();
            $table->string('shaba_number')->nullable();
            $table->string('fax_number')->nullable();
            $table->text('about_us')->nullable();
            $table->string('manager_national_code')->nullable();
            $table->string('manager_first_name')->nullable();
            $table->string('manager_last_name')->nullable();
            $table->string('manager_picture')->nullable();

            $table->string('account_number')->nullable();
            $table->string('card_number')->nullable();
            $table->string('card_owner_name')->nullable();

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
        Schema::drop('stores');
    }
}
