<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReadMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()    
    {
        Schema::create('read_messages', function (Blueprint $table) {
            $table->integer('message_id')->references('id')->on('messages');
            $table->integer('user_id')->references('id')->on('users');

            $table->primary(['user_id','message_id']);
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
        Schema::drop('read_messages');
    }
}
