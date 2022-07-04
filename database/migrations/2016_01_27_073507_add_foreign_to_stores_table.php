<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignToStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::table('store_phones', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });
        Schema::table('store_menus', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

        });
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('department_store', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_phones', function (Blueprint $table) {
            $table->dropForeign('store_phones_store_id_foreign');

        });
        Schema::table('store_menus', function (Blueprint $table) {
            $table->dropForeign('store_menus_store_id_foreign');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('products_store_id_foreign');
            $table->dropForeign('products_category_id_foreign');

        });
        Schema::table('department_store', function (Blueprint $table) {
            $table->dropForeign('department_store_store_id_foreign');
            $table->dropForeign('department_store_department_id_foreign');

        });
    }
}
