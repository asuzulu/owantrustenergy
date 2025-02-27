<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number');
            $table->string('gender');
            $table->string('street');
            $table->string('city');
            $table->string('state');
            $table->string('bvn')->unique();
            $table->string('nin')->unique();
            $table->string('email')->unique();
            $table->date('dob');
            $table->string('photo_id')->nullable();
            $table->string('password');
            $table->string('profile_image')->nullable();
            $table->string('position')->nullable()->default('Customer');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
