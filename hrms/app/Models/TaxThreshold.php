<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxThreshold extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'threshold_amount',
        'description',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'threshold_amount' => 'decimal:2',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
