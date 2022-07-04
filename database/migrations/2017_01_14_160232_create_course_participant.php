<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCourseParticipant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_participant', function (Blueprint $table) {
            $table->integer('course_id')->unsigned();
            $table->integer('participant_id')->unsigned();

            $table->foreign('course_id')->references('id')->on('courses');
            $table->foreign('participant_id')->references('id')->on('participants');
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
        Schema::drop('course_participant');
    }
}
