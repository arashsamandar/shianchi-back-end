<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateBazaarStaffTable extends Migration
{
    public function up()
    {
        Schema::create('bazaar_staff',function(Blueprint $table){
            $table->increments('id');
            $table->integer('bazaar_id')->unsigned();
            $table->integer('staff_id')->unsigned();

            $table->foreign('bazaar_id')->references('id')->on('bazaars')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('staffs')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::drop('bazaar_staff');
    }
}
