<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateRejectionMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rejection_messages',function(Blueprint $table){
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->string('message');
            $table->char('is_read')->default('0'); // 0 means no 1 means yes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::drop('rejection_messages');
    }
}
