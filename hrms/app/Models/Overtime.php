<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Overtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_member_id',
        'title',
        'number_of_days',
        'hours',
        'rate',
        'start_date',
        'end_date',
        'total_amount',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'number_of_days' => 'integer',
            'hours' => 'decimal:2',
            'rate' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
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

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($overtime) {
            $overtime->total_amount = $overtime->number_of_days * $overtime->hours * $overtime->rate;
        });

        static::updating(function ($overtime) {
            $overtime->total_amount = $overtime->number_of_days * $overtime->hours * $overtime->rate;
        });
    }
}
