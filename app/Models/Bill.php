<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'utility_account_id',
        'utility_service_id',
        'period',
        'amount',
        'due_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date:Y-m-d',
        ];
    }

    public function utilityAccount(): BelongsTo
    {
        return $this->belongsTo(UtilityAccount::class);
    }

    public function utilityService(): BelongsTo
    {
        return $this->belongsTo(UtilityService::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
