<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuaranteesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     *
     * type = 1 change for dissatisfy
     * type = 2 return mony for dissatisfy
     * type = 3 change issue in product
     * type = 4 return mony issue in product
     */
    public function up()
    {
        Schema::create('guarantees', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');

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
        Schema::drop('guarantees');
    }
}
