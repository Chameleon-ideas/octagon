<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSportsCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sport_countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id');
            $table->foreignId('country_id')->nullable();
            $table->string('country_name')->nullable();
            $table->string('country_iso_name')->nullable();
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
        Schema::dropIfExists('sports_countries');
    }
}
