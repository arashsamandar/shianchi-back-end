<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShippingCompanyStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_company_store', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('store_id')->unsigned();
            $table->integer('shipping_company_id')->unsigned();

            $table->foreign('store_id')
                ->references('id')
                ->on('stores')
                ->onDelete('cascade');

            $table->foreign('shipping_company_id')
                ->references('id')
                ->on('shipping_companies')
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
        Schema::drop('shipping_company_store');
    }
}
