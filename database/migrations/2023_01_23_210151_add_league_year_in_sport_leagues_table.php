<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeagueYearInSportLeaguesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sport_leagues', function (Blueprint $table) {
            $table->string('league_year')->nullable()->after('league_name');
            $table->string('country_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sport_leagues', function (Blueprint $table) {
            $table->dropColumn('league_year');
        });
    }
}
