<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllowanceTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'allowance_id',
        'tax_percentage',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'tax_percentage' => 'decimal:2',
        ];
    }

    public function allowance(): BelongsTo
    {
        return $this->belongsTo(Allowance::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
