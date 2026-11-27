<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class LogUtilityAccountCreated implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $accountId, public readonly int $ownerId) {}

    public function handle(): void
    {
        Log::info('Особовий рахунок створено', [
            'account_id' => $this->accountId,
            'owner_id' => $this->ownerId,
        ]);
    }
}
