<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_member_id',
        'date',
        'status',
        'clock_in',
        'clock_out',
        'late_minutes',
        'early_leaving_minutes',
        'overtime_minutes',
        'total_rest_minutes',
        'total_work_minutes',
        'notes',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'clock_in' => 'datetime:H:i:s',
            'clock_out' => 'datetime:H:i:s',
            'late_minutes' => 'integer',
            'early_leaving_minutes' => 'integer',
            'overtime_minutes' => 'integer',
            'total_rest_minutes' => 'integer',
            'total_work_minutes' => 'integer',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
