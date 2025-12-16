<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_member_id',
        'file_category_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'notes',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function fileCategory(): BelongsTo
    {
        return $this->belongsTo(FileCategory::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
