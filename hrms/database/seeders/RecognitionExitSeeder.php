<?php

namespace Database\Seeders;

use App\Models\ExitCategory;
use App\Models\RecognitionCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecognitionExitSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@hrms.com')->first();
        $authorId = $admin?->id ?? 1;

        $recognitionCategories = [
            ['title' => 'Star Performer', 'notes' => 'Recognition for outstanding performance'],
            ['title' => 'Team Champion', 'notes' => 'Recognition for excellent teamwork'],
            ['title' => 'Innovation Leader', 'notes' => 'Recognition for innovative ideas'],
            ['title' => 'Customer Hero', 'notes' => 'Recognition for exceptional customer service'],
            ['title' => 'Mentor of the Month', 'notes' => 'Recognition for mentoring colleagues'],
            ['title' => 'Safety Champion', 'notes' => 'Recognition for promoting workplace safety'],
            ['title' => 'Long Service Award', 'notes' => 'Recognition for years of service'],
        ];

        foreach ($recognitionCategories as $category) {
            RecognitionCategory::firstOrCreate(
                ['title' => $category['title']],
                [
                    'notes' => $category['notes'],
                    'is_active' => true,
                    'author_id' => $authorId,
                ]
            );
        }

        $exitCategories = [
            ['title' => 'Voluntary Resignation', 'notes' => 'Employee initiated resignation'],
            ['title' => 'Retirement', 'notes' => 'Retirement from service'],
            ['title' => 'Contract End', 'notes' => 'End of employment contract'],
            ['title' => 'Termination', 'notes' => 'Employer initiated termination'],
            ['title' => 'Layoff', 'notes' => 'Position elimination or downsizing'],
            ['title' => 'Medical Separation', 'notes' => 'Separation due to medical reasons'],
            ['title' => 'Death', 'notes' => 'Death of employee'],
        ];

        foreach ($exitCategories as $category) {
            ExitCategory::firstOrCreate(
                ['title' => $category['title']],
                [
                    'notes' => $category['notes'],
                    'is_active' => true,
                ]
            );
        }
    }
}
