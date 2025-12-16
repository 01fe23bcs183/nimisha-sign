<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecognitionRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_member_id',
        'recognition_category_id',
        'recognition_date',
        'reward',
        'notes',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'recognition_date' => 'date',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function recognitionCategory(): BelongsTo
    {
        return $this->belongsTo(RecognitionCategory::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
