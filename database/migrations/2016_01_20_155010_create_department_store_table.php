<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepartmentStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('department_store', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('store_id')->unsigned();
            $table->integer('department_id')->unsigned();

            $table->string('department_prefix_phone_number');
            $table->string('department_phone_number');
            $table->string('department_email');

            $table->string('department_manager_first_name');
            $table->string('department_manager_last_name');

            $table->string('department_manager_picture');

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
        Schema::drop('department_store');
    }
}
