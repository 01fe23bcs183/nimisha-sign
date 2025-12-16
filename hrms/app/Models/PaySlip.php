<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaySlip extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_member_id',
        'net_payable',
        'basic_salary',
        'salary_month',
        'status',
        'allowance',
        'commission',
        'loan',
        'saturation_deduction',
        'other_payment',
        'overtime',
        'company_contribution',
        'tax_bracket',
        'total_allowance',
        'total_commission',
        'total_loan',
        'total_deduction',
        'total_other_payment',
        'total_overtime',
        'total_company_contribution',
        'tax_amount',
        'gross_salary',
        'payment_date',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'net_payable' => 'decimal:2',
            'basic_salary' => 'decimal:2',
            'allowance' => 'array',
            'commission' => 'array',
            'loan' => 'array',
            'saturation_deduction' => 'array',
            'other_payment' => 'array',
            'overtime' => 'array',
            'company_contribution' => 'array',
            'tax_bracket' => 'array',
            'total_allowance' => 'decimal:2',
            'total_commission' => 'decimal:2',
            'total_loan' => 'decimal:2',
            'total_deduction' => 'decimal:2',
            'total_other_payment' => 'decimal:2',
            'total_overtime' => 'decimal:2',
            'total_company_contribution' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'payment_date' => 'date',
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
