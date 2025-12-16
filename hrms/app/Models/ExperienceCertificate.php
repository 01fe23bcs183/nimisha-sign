<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienceCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'lang',
        'content',
        'tenant_id',
        'author_id',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function generateForStaffMember(StaffMember $staffMember): string
    {
        $content = $this->content;
        
        $replacements = [
            '{employee_name}' => $staffMember->full_name,
            '{designation}' => $staffMember->jobTitle?->title ?? '',
            '{department}' => $staffMember->division?->title ?? '',
            '{branch}' => $staffMember->officeLocation?->title ?? '',
            '{join_date}' => $staffMember->hire_date?->format('d M Y') ?? '',
            '{end_date}' => now()->format('d M Y'),
            '{date}' => now()->format('d M Y'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}
