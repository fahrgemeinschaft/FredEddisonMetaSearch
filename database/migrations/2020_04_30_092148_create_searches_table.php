<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('searches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tripTypes', 10)->nullable();
            $table->string('reoccurDays', 10)->nullable();
            $table->string('smoking', 10)->nullable();
            $table->string('animals', 10)->nullable();
            $table->string('transportTypes', 10)->nullable();
            $table->string('baggage', 10)->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('organizations', 10)->nullable();
            $table->string('availabilityStarts', 10)->nullable();
            $table->string('availabilityEnds', 10)->nullable();
            $table->uuid('startPoint_id')->nullable();
            $table->uuid('endPoint_id')->nullable();
            $table->uuid('departure_id')->nullable();
            $table->uuid('arrival_id')->nullable();
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
        Schema::dropIfExists('searches');
    }
}
