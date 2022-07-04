<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeOrderProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_product', function (Blueprint $table) {
//            $table->dropForeign(['product_id']);
//            $table->dropColumn('product_id');
            $table->dropForeign(['buyer_address_id']);
            $table->dropColumn('buyer_address_id');
            $table->dropColumn('shipping_price');
            $table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
            $table->dropColumn('shipping_status');
            $table->dropColumn('shipping_id');
            $table->dropColumn('shipping_company_code');
            $table->dropColumn('wego_coin_get');
            $table->dropColumn('wego_coin_use');
            $table->dropColumn('delivery_date');
            $table->dropColumn('delivery_time');
            $table->integer('detail_id')->unsigned()->nullable();
            $table->foreign('detail_id')
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
        Schema::table('order_product', function (Blueprint $table) {
            $table->dropForeign(['detail_id']);
            $table->dropColumn('detail_id');
        });
    }
}
