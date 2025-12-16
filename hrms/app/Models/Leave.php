<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_member_id',
        'user_id',
        'leave_type_id',
        'applied_on',
        'start_date',
        'end_date',
        'total_leave_days',
        'leave_reason',
        'remark',
        'status',
        'approved_by',
        'approved_date',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'applied_on' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'total_leave_days' => 'decimal:1',
            'approved_date' => 'date',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($leave) {
            if (!$leave->total_leave_days) {
                $leave->total_leave_days = $leave->start_date->diffInDays($leave->end_date) + 1;
            }
        });
    }
}
