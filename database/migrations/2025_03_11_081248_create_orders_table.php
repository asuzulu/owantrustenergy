<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('cylinder_size');
            $table->string('weight');
            $table->string('retrieval'); // New field for retrieval option

            // Use enum type on databases that support it; use a plain string for SQLite.
            if (DB::getDriverName() === 'sqlite') {
                $table->string('order_type');
            } else {
                $table->enum('order_type', ['new', 'refill']);
            }

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
