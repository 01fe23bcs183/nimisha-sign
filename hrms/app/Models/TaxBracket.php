<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxBracket extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'from_amount',
        'to_amount',
        'fixed_amount',
        'percentage',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'from_amount' => 'decimal:2',
            'to_amount' => 'decimal:2',
            'fixed_amount' => 'decimal:2',
            'percentage' => 'decimal:2',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public static function calculateTax(float $amount): float
    {
        $bracket = self::where('from_amount', '<=', $amount)
            ->where('to_amount', '>=', $amount)
            ->first();

        if (!$bracket) {
            return 0;
        }

        return $bracket->fixed_amount + (($amount - $bracket->from_amount) * $bracket->percentage / 100);
    }
}
