<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCouponToGameOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('game_orders', function (Blueprint $table) {
            $table->string('alias_name')->nullable();
            $table->string('coupon_id')->nullable();
            $table->foreign('coupon_id')->references('id')->on('coupons')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('game_orders', function (Blueprint $table) {
            $table->dropColumn('alias_name');
            $table->dropForeign('game_orders_coupon_id_foreign');
            $table->dropColumn('coupon_id');
        });
    }
}
