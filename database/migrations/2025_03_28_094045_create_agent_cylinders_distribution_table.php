<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentCylindersDistributionTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('agent_cylinders_distribution', function (Blueprint $table) {
            $table->id();
            $table->string('agent_name');
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('cylinder_id');
            $table->string('cylinder_size');
            $table->string('cylinder_weight');
            $table->string('warehouse');
            $table->date('pick_up_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('agent_cylinders_distribution');
    }
}
