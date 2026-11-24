<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'edrpou',
        'bank_account',
        'contact_email',
    ];

    public function utilityServices(): HasMany
    {
        return $this->hasMany(UtilityService::class);
    }
}
