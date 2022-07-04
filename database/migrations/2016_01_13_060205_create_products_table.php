<?php

use App\Product;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id')->unique();

            $table->string('english_name');
            $table->softDeletes();
            $table->string('persian_name');
            $table->string('key_name');
            $table->text('description')->nullable();
            $table->char('confirmation_status',1)->default(Product::PRE_CONFIRMATION);

            $table->integer('weight');
            $table->integer('wego_coin_need')->nullable();


            $table->integer('length');
            $table->integer('width');
            $table->integer('height');

            $table->integer('quantity')->nullable();

            $table->string('warranty_name')->nullable();
            $table->text('warranty_text')->nullable();

            $table->integer('store_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->integer('current_price');
            $table->integer('view_count')->default(0);
            $table->integer('brand_id')->unsigned()->nullable();
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
        Schema::drop('products');
    }
}
