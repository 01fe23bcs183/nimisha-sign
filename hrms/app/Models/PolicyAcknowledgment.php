<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyAcknowledgment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_policy_id',
        'staff_member_id',
        'is_acknowledged',
        'acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'is_acknowledged' => 'boolean',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function companyPolicy(): BelongsTo
    {
        return $this->belongsTo(CompanyPolicy::class);
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }
}
