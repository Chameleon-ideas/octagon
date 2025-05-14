<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSportLeaguesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sport_leagues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id');
            $table->foreignId('country_id');
            $table->foreignId('league_id')->nullable();
            $table->string('league_name')->nullable();
            $table->text('logo_url')->nullable();
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
        Schema::dropIfExists('sport_leagues');
    }
}
