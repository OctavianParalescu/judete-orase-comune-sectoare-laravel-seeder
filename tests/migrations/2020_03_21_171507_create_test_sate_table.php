<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestSateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_sate', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->char('name', 255)->index();
            $table->char('coords', 255)->index();
            $table->unsignedBigInteger('county_id')->index();
            $table->foreign('county_id')->references('id')->on('test_counties');
            $table->unsignedBigInteger('city_id')->index();
            $table->foreign('city_id')->references('id')->on('test_cities');
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
        Schema::dropIfExists('test_sate');
    }
}
