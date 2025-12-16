<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@hrms.com')->first();
        $authorId = $admin?->id ?? 1;

        $documentTypes = [
            ['name' => 'Policy Document', 'description' => 'Company policies and procedures'],
            ['name' => 'Form', 'description' => 'HR forms and templates'],
            ['name' => 'Manual', 'description' => 'Employee handbooks and manuals'],
            ['name' => 'Training Material', 'description' => 'Training documents and presentations'],
            ['name' => 'Legal Document', 'description' => 'Legal and compliance documents'],
            ['name' => 'Report', 'description' => 'Reports and analytics'],
            ['name' => 'Other', 'description' => 'Other documents'],
        ];

        foreach ($documentTypes as $type) {
            DocumentType::firstOrCreate(
                ['name' => $type['name']],
                [
                    'description' => $type['description'],
                    'is_active' => true,
                    'author_id' => $authorId,
                ]
            );
        }
    }
}
