<?php

namespace Database\Seeders;

use App\Models\Holiday;
use App\Models\User;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@hrms.com')->first();
        $authorId = $admin?->id ?? 1;
        $year = date('Y');

        $holidays = [
            ['name' => "New Year's Day", 'date' => "{$year}-01-01", 'description' => 'New Year celebration'],
            ['name' => 'Martin Luther King Jr. Day', 'date' => "{$year}-01-15", 'description' => 'MLK Day'],
            ['name' => "Presidents' Day", 'date' => "{$year}-02-19", 'description' => 'Presidents Day'],
            ['name' => 'Memorial Day', 'date' => "{$year}-05-27", 'description' => 'Memorial Day'],
            ['name' => 'Independence Day', 'date' => "{$year}-07-04", 'description' => 'Independence Day'],
            ['name' => 'Labor Day', 'date' => "{$year}-09-02", 'description' => 'Labor Day'],
            ['name' => 'Columbus Day', 'date' => "{$year}-10-14", 'description' => 'Columbus Day'],
            ['name' => 'Veterans Day', 'date' => "{$year}-11-11", 'description' => 'Veterans Day'],
            ['name' => 'Thanksgiving Day', 'date' => "{$year}-11-28", 'description' => 'Thanksgiving'],
            ['name' => 'Christmas Day', 'date' => "{$year}-12-25", 'description' => 'Christmas celebration'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(
                ['name' => $holiday['name'], 'date' => $holiday['date']],
                [
                    'description' => $holiday['description'],
                    'is_active' => true,
                    'author_id' => $authorId,
                ]
            );
        }
    }
}
