<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'employee_prefix',
                'value' => 'EMP',
                'type' => 'string',
                'description' => 'Prefix for employee codes',
            ],
            [
                'key' => 'company_start_time',
                'value' => '09:00:00',
                'type' => 'string',
                'description' => 'Company work start time',
            ],
            [
                'key' => 'company_end_time',
                'value' => '18:00:00',
                'type' => 'string',
                'description' => 'Company work end time',
            ],
            [
                'key' => 'ip_restrict',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable IP restriction for attendance',
            ],
            [
                'key' => 'work_hours_per_day',
                'value' => '8',
                'type' => 'integer',
                'description' => 'Standard work hours per day',
            ],
            [
                'key' => 'overtime_rate',
                'value' => '1.5',
                'type' => 'float',
                'description' => 'Overtime rate multiplier',
            ],
            [
                'key' => 'late_deduction_rate',
                'value' => '0.5',
                'type' => 'float',
                'description' => 'Late arrival deduction rate per minute',
            ],
            [
                'key' => 'company_name',
                'value' => 'HRMS Company',
                'type' => 'string',
                'description' => 'Company name',
            ],
            [
                'key' => 'company_email',
                'value' => 'info@hrms.com',
                'type' => 'string',
                'description' => 'Company email address',
            ],
            [
                'key' => 'company_phone',
                'value' => '+1-555-0100',
                'type' => 'string',
                'description' => 'Company phone number',
            ],
            [
                'key' => 'company_address',
                'value' => '123 Business Street, City, Country',
                'type' => 'string',
                'description' => 'Company address',
            ],
            [
                'key' => 'currency',
                'value' => 'USD',
                'type' => 'string',
                'description' => 'Default currency',
            ],
            [
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'type' => 'string',
                'description' => 'Date format',
            ],
            [
                'key' => 'time_format',
                'value' => 'H:i:s',
                'type' => 'string',
                'description' => 'Time format',
            ],
        ];

        foreach ($settings as $setting) {
            CompanySetting::firstOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'description' => $setting['description'],
                ]
            );
        }
    }
}
