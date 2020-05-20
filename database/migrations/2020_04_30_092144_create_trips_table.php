<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamp('created')->nullable();
            $table->timestamp('modified')->nullable();
            $table->tinyInteger('deleted')->nullable();
            $table->string('createdBy', 36)->nullable();
            $table->string('modifiedBy', 36)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('additionalType', 36)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('image', 500)->nullable();
            $table->string('description', 2000)->nullable();
            $table->timestamp('arrivalTime')->nullable();
            $table->integer('availableSeats')->nullable();
            $table->string('connector');
            $table->string('smoking')->nullable();
            $table->string('animals')->nullable();
            $table->uuid('offer_id')->nullable();
            $table->uuid('demand_id')->nullable();
            $table->uuid('transport_id')->nullable();
            $table->uuid('participation_id')->nullable();
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
        Schema::dropIfExists('trips');
    }
}
