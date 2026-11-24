<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'utility_account_id',
        'previous_value',
        'current_value',
        'submitted_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'previous_value' => 'decimal:3',
            'current_value' => 'decimal:3',
            'submitted_at' => 'datetime',
        ];
    }

    public function utilityAccount(): BelongsTo
    {
        return $this->belongsTo(UtilityAccount::class);
    }
}
