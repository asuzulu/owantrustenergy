<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            
            [
                'first_name' => 'Set',
                'last_name' => 'Customer',
                'phone_number' => '08011112222',
                'gender' => 'male',
                'street' => '12 Market Road',
                'city' => 'Onitsha',
                'state' => 'Anambra',
                'bvn' => '11112222333',
                'nin' => '12345098765',
                'email' => 'setcustomer@gmail.com',
                'dob' => '1990-07-21',
                'password' => Hash::make('password'),
                'profile_image' => null,
                'position' => 'Customer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Set',
                'last_name' => 'Manager',
                'phone_number' => '08033334444',
                'gender' => 'male',
                'street' => '8 Industrial Street',
                'city' => 'Abeokuta',
                'state' => 'Ogun',
                'bvn' => '22223333444',
                'nin' => '54321987654',
                'email' => 'setmanager@gmail.com',
                'dob' => '1982-05-15',
                'password' => Hash::make('password'),
                'profile_image' => null,
                'position' => 'Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Set',
                'last_name' => 'Employee',
                'phone_number' => '08055556666',
                'gender' => 'male',
                'street' => '5 Kingsway',
                'city' => 'Ibadan',
                'state' => 'Oyo',
                'bvn' => '33334444555',
                'nin' => '67890123456',
                'email' => 'setemployee@gmail.com',
                'dob' => '1995-02-10',
                'password' => Hash::make('password'),
                'profile_image' => null,
                'position' => 'Employee',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Set',
                'last_name' => 'Agent',
                'phone_number' => '08077778888',
                'gender' => 'male',
                'street' => '21 Freedom Street',
                'city' => 'Enugu',
                'state' => 'Enugu',
                'bvn' => '44445555666',
                'nin' => '78901234567',
                'email' => 'setagent@gmail.com',
                'dob' => '1988-11-30',
                'password' => Hash::make('password'),
                'profile_image' => null,
                'position' => 'Agent',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Set',
                'last_name' => 'Driver',
                'phone_number' => '08099990000',
                'gender' => 'male',
                'street' => '10 Express Road',
                'city' => 'Kano',
                'state' => 'Kano',
                'bvn' => '55556666777',
                'nin' => '89012345678',
                'email' => 'setdriver@gmail.com',
                'dob' => '1991-09-18',
                'password' => Hash::make('password'),
                'profile_image' => null,
                'position' => 'Driver',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
