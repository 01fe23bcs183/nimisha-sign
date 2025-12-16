<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            OrganizationSeeder::class,
            LeaveTypeSeeder::class,
            PayrollOptionSeeder::class,
            TaxBracketSeeder::class,
            RecognitionExitSeeder::class,
            CompanySettingSeeder::class,
            DocumentTypeSeeder::class,
            HolidaySeeder::class,
            LetterTemplateSeeder::class,
        ]);
    }
}
