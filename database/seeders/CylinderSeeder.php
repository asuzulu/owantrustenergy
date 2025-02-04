<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CylinderSeeder extends Seeder
{
    public function run()
    {
        $sizes = ['Small', 'Medium', 'Large']; // Example sizes
        $location = 'Warehouse 1'; // Example location

        $cylinders = [];

        // Generate 25 cylinders with unique 'id'
        for ($i = 1; $i <= 25; $i++) {
            $serialNumber = str_pad($i, 9, '0', STR_PAD_LEFT); // Generate 9-digit serial number (000000001 - 000000025)
            $cylinders[] = [
                'id' => $serialNumber, // Use $serialNumber as the 'id'
                'size' => $sizes[array_rand($sizes)], // Randomly pick a size
                'location' => $location,
                'allocated_date' => now(),
                'user_id' => null, // Not assigned to any user
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert the cylinders into the 'cylinders' table
        DB::table('cylinders')->insert($cylinders);
    }
}
