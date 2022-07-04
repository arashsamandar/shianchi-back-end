<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnsOfGameScoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('game_scores', function (Blueprint $table) {
            $table->dropColumn('telegram_username');
            $table->dropColumn('call_number');
            $table->dropColumn('email');
            $table->string('contact_string');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('game_scores', function (Blueprint $table) {
            $table->dropColumn('contact_string');
        });
    }
}
