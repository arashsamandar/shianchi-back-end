<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCourseOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_order', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('total_price');
            $table->integer('participant_id');
            $table->integer('total_discount');
            $table->integer('status')->default(\App\CourseOrder::NOT_PAYED);
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
        Schema::drop('course_order');
    }
}
