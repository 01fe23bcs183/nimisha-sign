<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaturationDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_member_id',
        'deduction_option_id',
        'title',
        'type',
        'amount',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function deductionOption(): BelongsTo
    {
        return $this->belongsTo(DeductionOption::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
