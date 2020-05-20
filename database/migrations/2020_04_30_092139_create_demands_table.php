<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demands', function (Blueprint $table) {
            $table->uuid('id');
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
            $table->string('availability', 500)->nullable();
            $table->timestamp('availabilityStarts')->nullable();
            $table->timestamp('availabilityEnds')->nullable();
            $table->string('price', 64)->nullable();
            $table->string('priceCurrency', 10)->nullable();
            $table->uuid('trip_id')->nullable();
            $table->uuid('persona_id')->nullable();
            $table->timestamps();
            $table->primary('id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('demands');
    }
}
