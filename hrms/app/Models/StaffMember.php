<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'full_name',
        'personal_email',
        'mobile_number',
        'birth_date',
        'gender',
        'home_address',
        'nationality',
        'passport_number',
        'country_code',
        'region',
        'city_name',
        'postal_code',
        'staff_code',
        'biometric_id',
        'office_location_id',
        'division_id',
        'job_title_id',
        'hire_date',
        'bank_account_name',
        'bank_account_number',
        'bank_name',
        'bank_branch',
        'compensation_type',
        'base_salary',
        'employment_status',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'marital_status',
        'dependents_count',
        'tax_identification_number',
        'social_security_number',
        'profile_photo',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'hire_date' => 'date',
            'base_salary' => 'decimal:2',
            'dependents_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(OfficeLocation::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function staffFiles(): HasMany
    {
        return $this->hasMany(StaffFile::class);
    }

    public function recognitionRecords(): HasMany
    {
        return $this->hasMany(RecognitionRecord::class);
    }

    public function roleUpgrades(): HasMany
    {
        return $this->hasMany(RoleUpgrade::class);
    }

    public function locationTransfers(): HasMany
    {
        return $this->hasMany(LocationTransfer::class);
    }

    public function disciplineNotes(): HasMany
    {
        return $this->hasMany(DisciplineNote::class);
    }

    public function offboardings(): HasMany
    {
        return $this->hasMany(Offboarding::class);
    }

    public function voluntaryExits(): HasMany
    {
        return $this->hasMany(VoluntaryExit::class);
    }

    public function travels(): HasMany
    {
        return $this->hasMany(Travel::class);
    }

    public function complaintsFrom(): HasMany
    {
        return $this->hasMany(Complaint::class, 'complaint_from');
    }

    public function complaintsAgainst(): HasMany
    {
        return $this->hasMany(Complaint::class, 'complaint_against');
    }

    public function announcements(): BelongsToMany
    {
        return $this->belongsToMany(Announcement::class)
            ->withPivot('is_read', 'read_at')
            ->withTimestamps();
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(Allowance::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function saturationDeductions(): HasMany
    {
        return $this->hasMany(SaturationDeduction::class);
    }

    public function otherPayments(): HasMany
    {
        return $this->hasMany(OtherPayment::class);
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class);
    }

    public function companyContributions(): HasMany
    {
        return $this->hasMany(CompanyContribution::class);
    }

    public function paySlips(): HasMany
    {
        return $this->hasMany(PaySlip::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class)
            ->withPivot('is_notified', 'notified_at')
            ->withTimestamps();
    }

    public function policyAcknowledgments(): HasMany
    {
        return $this->hasMany(PolicyAcknowledgment::class);
    }

    public function getNetSalary(): float
    {
        $basic = (float) $this->base_salary;
        
        $totalAllowances = $this->allowances->sum(function ($allowance) use ($basic) {
            return $allowance->type === 'percentage' 
                ? ($basic * $allowance->amount / 100) 
                : $allowance->amount;
        });

        $totalCommissions = $this->commissions()
            ->where('start_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->get()
            ->sum(function ($commission) use ($basic) {
                return $commission->type === 'percentage' 
                    ? ($basic * $commission->amount / 100) 
                    : $commission->amount;
            });

        $totalOvertimes = $this->overtimes()
            ->where('start_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->sum('total_amount');

        $totalOtherPayments = $this->otherPayments->sum(function ($payment) use ($basic) {
            return $payment->type === 'percentage' 
                ? ($basic * $payment->amount / 100) 
                : $payment->amount;
        });

        $totalCompanyContributions = $this->companyContributions->sum(function ($contribution) use ($basic) {
            return $contribution->type === 'percentage' 
                ? ($basic * $contribution->amount / 100) 
                : $contribution->amount;
        });

        $totalLoans = $this->loans()
            ->where('status', 'active')
            ->sum('monthly_deduction');

        $totalDeductions = $this->saturationDeductions->sum(function ($deduction) use ($basic) {
            return $deduction->type === 'percentage' 
                ? ($basic * $deduction->amount / 100) 
                : $deduction->amount;
        });

        $grossSalary = $basic + $totalAllowances + $totalCommissions + $totalOvertimes + $totalOtherPayments + $totalCompanyContributions;
        $netSalary = $grossSalary - $totalLoans - $totalDeductions;

        return max(0, $netSalary);
    }
}
