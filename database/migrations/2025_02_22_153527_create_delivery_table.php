<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->string('driver'); // Stores the driver's full name
            $table->unsignedBigInteger('cylinder');
            $table->string('size');
            $table->string('address');
            $table->string('customer');
            $table->date('date_assigned');
            $table->date('delivery_date');
            $table->time('delivery_time');
            $table->date('date_delivered')->nullable();
            $table->time('time_delivered')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
};
