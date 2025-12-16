<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@hrms.com')->first();
        $authorId = $admin?->id ?? 1;

        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'days' => 20,
                'description' => 'Paid annual vacation leave',
            ],
            [
                'name' => 'Sick Leave',
                'days' => 12,
                'description' => 'Leave for medical reasons',
            ],
            [
                'name' => 'Casual Leave',
                'days' => 10,
                'description' => 'Leave for personal matters',
            ],
            [
                'name' => 'Maternity Leave',
                'days' => 90,
                'description' => 'Leave for expecting mothers',
            ],
            [
                'name' => 'Paternity Leave',
                'days' => 14,
                'description' => 'Leave for new fathers',
            ],
            [
                'name' => 'Bereavement Leave',
                'days' => 5,
                'description' => 'Leave for family bereavement',
            ],
            [
                'name' => 'Unpaid Leave',
                'days' => 30,
                'description' => 'Leave without pay',
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::firstOrCreate(
                ['name' => $leaveType['name']],
                [
                    'days' => $leaveType['days'],
                    'description' => $leaveType['description'],
                    'is_active' => true,
                    'author_id' => $authorId,
                ]
            );
        }
    }
}
