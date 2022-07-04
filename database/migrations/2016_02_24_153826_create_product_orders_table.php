
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_product', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('order_id')->unsigned();
            $table->integer('product_id')->unsigned();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->integer('buyer_address_id')->unsigned();
            
            $table->text('gift');
            $table->integer('gift_count');

            $table->integer('shipping_price');
            $table->integer('payment_id')->unsigned();

            $table->string('shipping_status');

            $table->integer('shipping_id')->unsigned()->nullable();
            $table->string('shipping_company_code')->nullable();

            $table->integer('wego_coin_get');
            $table->integer('wego_coin_use');

            $table->foreign('buyer_address_id')
               ->references('id')->on('buyer_addresses')->onDelete('cascade');


            $table->integer('quantity');
            $table->integer('price');

            $table->integer('discount');

            $table->date('delivery_date');
            $table->string('delivery_time');

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
        Schema::drop('order_product');
    }
}
