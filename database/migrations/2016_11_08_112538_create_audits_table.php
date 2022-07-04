<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('store_id')->unsigned();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('type');
            $table->string('status')->default("not paid");
            $table->integer('crude_price');
            $table->integer('wego_rights');
            $table->integer('final_amount');
            $table->string('tracking_code');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('audits');
    }
}
