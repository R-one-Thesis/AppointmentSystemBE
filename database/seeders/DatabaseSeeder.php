<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $userTypes = [
            
            [
                'email' => 'patient@gmail.com',
                'password' => Hash::make('patient@gmail.com'),
                'user_type' => 'patient',
            ],
            [
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin@gmail.com'),
                'user_type' => 'admin',
            ],
        ];

        

        $admin = [
            [
                'user_id' => 2,
                'first_name' => 'Mark',
                'last_name' => 'Lao',
                'home_address' => 'Vamenta opol'
            ]
        ];

        $doctors = [
            [
                'dentist' => 'Dr. Smith',
                'mobile_number' => '123-456-7890',
                'email' => 'drsmith@example.com',
            ],
            
        ];

        User::insert($userTypes);
        
        Admin::insert($admin);
        Doctor::insert($doctors);
    }
}
