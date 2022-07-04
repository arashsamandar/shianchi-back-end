<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignToBazaarStore extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bazaar_store', function (Blueprint $table) {
            $table->integer('bazaar_id')->unsigned()->change();
            $table->integer('store_id')->unsigned()->change();

            $table->foreign('bazaar_id')->references('id')->on('bazaars')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bazaar_store', function (Blueprint $table) {

//            $table->integer('bazaar_id')->change();
//            $table->integer('store_id')->change();

            $table->dropForeign('bazaar_store_bazaar_id_foreign');
            $table->dropForeign('bazaar_store_store_id_foreign');
        });

    }
}
