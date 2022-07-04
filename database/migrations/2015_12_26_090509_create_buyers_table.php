<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuyersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buyers', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('last_name',50)->nullable();
            $table->string('mobile_number',15)->nullable();
            $table->string('landline_number',11)->nullable();
            $table->text('address')->nullable();
            $table->string('card_number')->nullable();
            $table->string('card_owner_name')->nullable();
            $table->string('image_path')->nullable();
            $table->string('gender')->nullable();
            $table->string('job_title')->nullable();
            $table->char('magazine_subscriber',1)->default('0');
            $table->timestamp('birthday')->nullable();
            $table->string('national_code',10)->nullable();
            $table->string('company_name',50)->nullable();
            $table->integer('comment_score')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('buyers');
    }
}
