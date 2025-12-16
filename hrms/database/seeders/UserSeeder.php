<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@hrms.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('administrator');

        $manager = User::firstOrCreate(
            ['email' => 'manager@hrms.com'],
            [
                'name' => 'Department Manager',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $manager->assignRole('manager');

        $hrOfficer = User::firstOrCreate(
            ['email' => 'hr@hrms.com'],
            [
                'name' => 'HR Officer',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $hrOfficer->assignRole('hr_officer');

        $staff = User::firstOrCreate(
            ['email' => 'staff@hrms.com'],
            [
                'name' => 'Staff Member',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $staff->assignRole('staff_member');
    }
}
