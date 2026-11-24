<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UtilityAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_number',
        'address',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function utilityServices(): BelongsToMany
    {
        return $this->belongsToMany(UtilityService::class)->withTimestamps();
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function meterReadings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }
}
