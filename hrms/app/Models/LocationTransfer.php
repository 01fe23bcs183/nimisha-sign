<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_member_id',
        'previous_office_location_id',
        'new_office_location_id',
        'previous_division_id',
        'new_division_id',
        'effective_date',
        'notes',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function previousOfficeLocation(): BelongsTo
    {
        return $this->belongsTo(OfficeLocation::class, 'previous_office_location_id');
    }

    public function newOfficeLocation(): BelongsTo
    {
        return $this->belongsTo(OfficeLocation::class, 'new_office_location_id');
    }

    public function previousDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'previous_division_id');
    }

    public function newDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'new_division_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
