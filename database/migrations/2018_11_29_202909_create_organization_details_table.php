<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganizationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_details', function (Blueprint $table) {
            $table->increments('id');
            $table->string('company_name');
            $table->string('economic_code');
            $table->string('postal_code');
            $table->text('address');
            $table->string('phone_number');
            $table->integer('order_id')->unsigned();
            $table->foreign('order_id')->references('id')
                ->on('orders')->onDelete('cascade');
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
        Schema::dropIfExists('organization_details');
    }
}
