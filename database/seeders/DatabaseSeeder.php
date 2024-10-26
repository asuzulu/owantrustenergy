<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Call the StatesTableSeeder
        $this->call(StatesTableSeeder::class);

        // Call the UsersTableSeeder
        $this->call([
            UsersTableSeeder::class,
        ]);
    }
}
