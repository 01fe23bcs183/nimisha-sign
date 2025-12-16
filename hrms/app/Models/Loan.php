<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_member_id',
        'loan_option_id',
        'title',
        'type',
        'amount',
        'start_date',
        'end_date',
        'reason',
        'installments',
        'monthly_deduction',
        'remaining_amount',
        'status',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'monthly_deduction' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'installments' => 'integer',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function loanOption(): BelongsTo
    {
        return $this->belongsTo(LoanOption::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($loan) {
            if ($loan->installments > 0 && !$loan->monthly_deduction) {
                $loan->monthly_deduction = $loan->amount / $loan->installments;
            }
            if (!$loan->remaining_amount) {
                $loan->remaining_amount = $loan->amount;
            }
        });
    }
}
