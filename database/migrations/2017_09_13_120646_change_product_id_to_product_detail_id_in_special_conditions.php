<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeProductIdToProductDetailIdInSpecialConditions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('special_conditions', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            $table->integer('product_detail_id')->unsigned()->nullable();
            $table->foreign('product_detail_id')
                ->references('id')->on('product_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('special_conditions', function (Blueprint $table) {
            $table->dropForeign(['product_detail_id']);
            $table->dropColumn('product_detail_id');
        });
    }
}
