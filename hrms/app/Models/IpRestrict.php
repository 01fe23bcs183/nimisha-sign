<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpRestrict extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'description',
        'is_active',
        'tenant_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public static function isIpAllowed(string $ip): bool
    {
        $activeRestrictions = self::where('is_active', true)->get();
        
        if ($activeRestrictions->isEmpty()) {
            return true;
        }

        return $activeRestrictions->contains('ip_address', $ip);
    }
}
