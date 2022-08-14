<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ship_positions', function (Blueprint $table) {
            $table->integer('mmsi')->unsigned();
            $table->integer('status');
            $table->integer('stationId');
            $table->integer('speed');
            $table->string('lon');
            $table->string('lat');
            //chose string for lon & lat to prevent a common error for fractions in double values,it can be 31.122 in the database,then it returns 31.1124548488855454 in the api response
            //will be cast to double with a getter check the model ShipPosition.php
            $table->integer('course');
            $table->integer('heading');
            $table->string('rot');
            $table->dateTime('timestamp');
            //had a problem with inserting unix timestamps in timestamp column, so I made a possible fix
            //will be casted to timestamp with a setter & getter, check the model ShipPosition.php
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ship_positions');
    }
}
