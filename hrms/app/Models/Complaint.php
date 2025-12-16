<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_from',
        'complaint_against',
        'complaint_against_division',
        'title',
        'complaint_date',
        'description',
        'status',
        'resolution',
        'resolution_date',
        'resolved_by',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'complaint_date' => 'date',
            'resolution_date' => 'date',
        ];
    }

    public function complainant(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class, 'complaint_from');
    }

    public function complainedAgainst(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class, 'complaint_against');
    }

    public function complainedAgainstDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'complaint_against_division');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
