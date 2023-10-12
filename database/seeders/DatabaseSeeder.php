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

        $patient = [
            [
                'user_id' => 1,
                'first_name' => 'Jim',
                'last_name' => 'Lao',
                'sex' => 'male',
                'home_address' => 'barra opol',
                'marital_status' => 'single'
            ]
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
                'specialization' => 'Dentistry',
                'mobile_number' => '123-456-7890',
                'email' => 'drsmith@example.com',
            ],
            [
                'dentist' => 'Dr. Johnson',
                'specialization' => 'Orthodontics',
                'mobile_number' => '987-654-3210',
                'email' => 'drjohnson@example.com',
            ],
            [
                'dentist' => 'Dr. Brown',
                'specialization' => 'Oral Surgery',
                'mobile_number' => '555-123-4567',
                'email' => 'drbrown@example.com',
            ],
            [
                'dentist' => 'Dr. Wilson',
                'specialization' => 'Pediatric Dentistry',
                'mobile_number' => '777-888-9999',
                'email' => 'drwilson@example.com',
            ],
            [
                'dentist' => 'Dr. Lee',
                'specialization' => 'Endodontics',
                'mobile_number' => '111-222-3333',
                'email' => 'drlee@example.com',
            ],
        ];

        User::insert($userTypes);
        Patient::insert($patient);
        Admin::insert($admin);
        Doctor::insert($doctors);
    }
}
