<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDeleteToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('buyer_addresses', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('product_details', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('warranties', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('colors', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('buyer_addresses', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('product_details', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('warranties', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('colors', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
