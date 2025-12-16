<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleUpgrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_member_id',
        'previous_job_title_id',
        'new_job_title_id',
        'upgrade_title',
        'effective_date',
        'notes',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function previousJobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class, 'previous_job_title_id');
    }

    public function newJobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class, 'new_job_title_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
