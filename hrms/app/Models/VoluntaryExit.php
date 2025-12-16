<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoluntaryExit extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_member_id',
        'notice_date',
        'exit_date',
        'reason',
        'approval_status',
        'approved_by',
        'approved_date',
        'approval_notes',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'notice_date' => 'date',
            'exit_date' => 'date',
            'approved_date' => 'date',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
