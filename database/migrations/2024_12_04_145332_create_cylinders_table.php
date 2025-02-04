<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCylindersTable extends Migration
{
    public function up()
    {
        Schema::create('cylinders', function (Blueprint $table) {
            $table->string('id', 9)->primary();
            $table->string('size');
            $table->string('location');
            $table->date('allocated_date')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('qr_code_path')->nullable();  // Add the QR code path
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cylinders');
    }
}
