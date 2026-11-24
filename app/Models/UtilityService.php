<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UtilityService extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider_id',
        'name',
        'unit',
        'tariff',
        'active_from',
    ];

    protected function casts(): array
    {
        return [
            'tariff' => 'decimal:2',
            'active_from' => 'date:Y-m-d',
        ];
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function utilityAccounts(): BelongsToMany
    {
        return $this->belongsToMany(UtilityAccount::class)->withTimestamps();
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }
}
