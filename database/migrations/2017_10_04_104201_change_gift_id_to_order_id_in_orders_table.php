<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeGiftIdToOrderIdInOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['user_id','gift_id']);
            $table->dropForeign(['gift_id']);
            $table->dropColumn('gift_id');
            $table->string('coupon_id')->nullable();
            $table->foreign('coupon_id')->references('id')->on('coupons')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['user_id','coupon_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['user_id','coupon_id']);
            $table->dropForeign(['coupon_id']);
            $table->dropColumn('coupon_id');
        });
    }
}
