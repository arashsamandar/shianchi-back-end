<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_status');
            $table->dropColumn('time');
            $table->string('delivery_time');
            $table->integer('address_id')->unsigned()->nullable();
            $table->integer('progressable')->default('0');
            $table->string('shipping_company');
            $table->string('shipping_status');
            $table->integer('shipping_price');
            $table->integer('final_products_price');
            $table->integer('final_order_price');
            $table->integer('payment_id');
            $table->integer('total_discount');
            $table->foreign('address_id')
                ->references('id')->on('buyer_addresses');
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
            $table->dropColumn('delivery_time');
            $table->dropForeign(['address_id']);
            $table->dropColumn('address_id');
            $table->dropColumn('progressable');
            $table->dropColumn('shipping_company');
            $table->dropColumn('shipping_status');
            $table->dropColumn('shipping_price');
            $table->dropColumn('final_products_price');
            $table->dropColumn('payment_id');
            $table->dropColumn('final_order_price');
            $table->dropColumn('total_discount');
        });
    }
}
