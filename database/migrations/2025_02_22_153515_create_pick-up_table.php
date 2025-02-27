<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pickups', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->unsignedBigInteger('cylinder');
            $table->string('size');
            $table->string('customer');
            $table->date('date_assigned');
            $table->date('pick_up_date');
            $table->date('date_picked_up')->nullable();
            $table->time('time_picked_up')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pickups');
    }
};
