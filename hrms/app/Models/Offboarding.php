<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offboarding extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_member_id',
        'exit_category_id',
        'exit_date',
        'notice_date',
        'details',
        'clearance_completed',
        'clearance_date',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'exit_date' => 'date',
            'notice_date' => 'date',
            'clearance_completed' => 'boolean',
            'clearance_date' => 'date',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function exitCategory(): BelongsTo
    {
        return $this->belongsTo(ExitCategory::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
