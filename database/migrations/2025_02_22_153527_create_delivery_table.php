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
            // Store the cylinder ID _as a string_ so that leading zeros are preserved
            $table->string('cylinder', 9);
            $table->unsignedBigInteger('driver_id');
            $table->string('driver');
            $table->date('driver_pickup_date')->nullable();
            $table->time('driver_pickup_time')->nullable();
            $table->string('size');
            $table->unsignedBigInteger('customer_id');
            $table->string('customer');
            $table->string('address');
            $table->string('date_assigned');
            $table->date('delivery_date');
            $table->time('delivery_time');
            $table->timestamp('delivery_start')->nullable();
            $table->date('date_delivered')->nullable();
            $table->time('time_delivered')->nullable();
            $table->string('image_path')->nullable();
            // approval status: 'approved', 'disapproved' or null
            $table->string('approval')->nullable();
            //  7-digit numeric passcode for pickup confirmation**
            $table->string('passcode', 7);
            $table->timestamps();

            // Foreign keys
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
};
