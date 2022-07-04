<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTransactions extends Migration
{
    public function up()
    {
        Schema::create('transactions',function(Blueprint $table){
            $table->increments('id');
            $table->integer('amount')->unsigned();
            $table->integer('element_id')->unsigned();
            $table->string('status');
            $table->string('service_type');
            $table->string('bank_name');
            $table->integer('user_id')->unsigned();
            $table->ipAddress('ip')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('transactions');
    }
}
