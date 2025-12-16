<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\FileCategory;
use App\Models\JobTitle;
use App\Models\OfficeLocation;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@hrms.com')->first();
        $authorId = $admin?->id ?? 1;

        $headquarters = OfficeLocation::firstOrCreate(
            ['title' => 'Headquarters'],
            [
                'address' => '123 Main Street, Business District',
                'contact_phone' => '+1-555-0100',
                'contact_email' => 'hq@company.com',
                'is_active' => true,
                'author_id' => $authorId,
            ]
        );

        $regionalOffice = OfficeLocation::firstOrCreate(
            ['title' => 'Regional Office - North'],
            [
                'address' => '456 North Avenue, Industrial Zone',
                'contact_phone' => '+1-555-0200',
                'contact_email' => 'north@company.com',
                'is_active' => true,
                'author_id' => $authorId,
            ]
        );

        $remoteOffice = OfficeLocation::firstOrCreate(
            ['title' => 'Remote Office - South'],
            [
                'address' => '789 South Boulevard, Tech Park',
                'contact_phone' => '+1-555-0300',
                'contact_email' => 'south@company.com',
                'is_active' => true,
                'author_id' => $authorId,
            ]
        );

        $hrDivision = Division::firstOrCreate(
            ['title' => 'Human Resources', 'office_location_id' => $headquarters->id],
            [
                'notes' => 'Handles all HR-related activities',
                'is_active' => true,
                'author_id' => $authorId,
            ]
        );

        $itDivision = Division::firstOrCreate(
            ['title' => 'Information Technology', 'office_location_id' => $headquarters->id],
            [
                'notes' => 'Manages IT infrastructure and software development',
                'is_active' => true,
                'author_id' => $authorId,
            ]
        );

        $financeDivision = Division::firstOrCreate(
            ['title' => 'Finance & Accounting', 'office_location_id' => $headquarters->id],
            [
                'notes' => 'Handles financial operations and accounting',
                'is_active' => true,
                'author_id' => $authorId,
            ]
        );

        $salesDivision = Division::firstOrCreate(
            ['title' => 'Sales & Marketing', 'office_location_id' => $regionalOffice->id],
            [
                'notes' => 'Manages sales and marketing activities',
                'is_active' => true,
                'author_id' => $authorId,
            ]
        );

        $operationsDivision = Division::firstOrCreate(
            ['title' => 'Operations', 'office_location_id' => $remoteOffice->id],
            [
                'notes' => 'Handles day-to-day operations',
                'is_active' => true,
                'author_id' => $authorId,
            ]
        );

        JobTitle::firstOrCreate(
            ['title' => 'HR Manager', 'division_id' => $hrDivision->id],
            ['notes' => 'Manages HR department', 'is_active' => true, 'author_id' => $authorId]
        );

        JobTitle::firstOrCreate(
            ['title' => 'HR Officer', 'division_id' => $hrDivision->id],
            ['notes' => 'Handles HR operations', 'is_active' => true, 'author_id' => $authorId]
        );

        JobTitle::firstOrCreate(
            ['title' => 'IT Manager', 'division_id' => $itDivision->id],
            ['notes' => 'Manages IT department', 'is_active' => true, 'author_id' => $authorId]
        );

        JobTitle::firstOrCreate(
            ['title' => 'Software Developer', 'division_id' => $itDivision->id],
            ['notes' => 'Develops software applications', 'is_active' => true, 'author_id' => $authorId]
        );

        JobTitle::firstOrCreate(
            ['title' => 'System Administrator', 'division_id' => $itDivision->id],
            ['notes' => 'Manages IT systems', 'is_active' => true, 'author_id' => $authorId]
        );

        JobTitle::firstOrCreate(
            ['title' => 'Finance Manager', 'division_id' => $financeDivision->id],
            ['notes' => 'Manages finance department', 'is_active' => true, 'author_id' => $authorId]
        );

        JobTitle::firstOrCreate(
            ['title' => 'Accountant', 'division_id' => $financeDivision->id],
            ['notes' => 'Handles accounting tasks', 'is_active' => true, 'author_id' => $authorId]
        );

        JobTitle::firstOrCreate(
            ['title' => 'Sales Manager', 'division_id' => $salesDivision->id],
            ['notes' => 'Manages sales team', 'is_active' => true, 'author_id' => $authorId]
        );

        JobTitle::firstOrCreate(
            ['title' => 'Sales Representative', 'division_id' => $salesDivision->id],
            ['notes' => 'Handles sales activities', 'is_active' => true, 'author_id' => $authorId]
        );

        JobTitle::firstOrCreate(
            ['title' => 'Operations Manager', 'division_id' => $operationsDivision->id],
            ['notes' => 'Manages operations', 'is_active' => true, 'author_id' => $authorId]
        );

        FileCategory::firstOrCreate(
            ['title' => 'Identity Documents'],
            ['notes' => 'Government-issued ID, passport, etc.', 'is_mandatory' => true, 'is_active' => true]
        );

        FileCategory::firstOrCreate(
            ['title' => 'Educational Certificates'],
            ['notes' => 'Degrees, diplomas, certifications', 'is_mandatory' => true, 'is_active' => true]
        );

        FileCategory::firstOrCreate(
            ['title' => 'Employment Documents'],
            ['notes' => 'Offer letter, contract, etc.', 'is_mandatory' => true, 'is_active' => true]
        );

        FileCategory::firstOrCreate(
            ['title' => 'Bank Details'],
            ['notes' => 'Bank account information', 'is_mandatory' => false, 'is_active' => true]
        );

        FileCategory::firstOrCreate(
            ['title' => 'Medical Records'],
            ['notes' => 'Health certificates, medical reports', 'is_mandatory' => false, 'is_active' => true]
        );
    }
}
