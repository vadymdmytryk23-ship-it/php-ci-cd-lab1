<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\UtilityAccount;
use App\Models\UtilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UtilityAccountAccessTest extends TestCase
{
    use RefreshDatabase;

    private array $accountData = [
        'account_number' => 'UA-150004',
        'address' => 'м. Київ, вул. Січових Стрільців, 15',
    ];

    public function test_accounts_can_be_received(): void
    {
        UtilityAccount::factory()->count(3)->create();
        $this->getJson('/api/v1/utility-accounts')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_existing_account_can_be_received(): void
    {
        $account = UtilityAccount::factory()->create(['account_number' => 'UA-150001']);
        $this->getJson('/api/v1/utility-accounts/'.$account->id)
            ->assertOk()
            ->assertJsonPath('data.id', $account->id)
            ->assertJsonMissingPath('data.account_number');
    }

    public function test_missing_account_returns_not_found(): void
    {
        $this->getJson('/api/v1/utility-accounts/999')
            ->assertNotFound();
    }

    public function test_request_without_bearer_token_is_blocked(): void
    {
        $this->postJson('/api/v1/utility-accounts', $this->accountData)
            ->assertUnauthorized();
    }

    public function test_request_with_valid_token_creates_account(): void
    {
        $user = User::factory()->create();
        $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson('/api/v1/utility-accounts', $this->accountData + [
                'service_ids' => [UtilityService::factory()->create()->id],
            ])
            ->assertCreated()
            ->assertJsonPath('data.owner_id', $user->id)
            ->assertJsonPath('data.account_number', 'UA-150004');
    }

    public function test_invalid_data_returns_validation_error(): void
    {
        $this->withToken(User::factory()->create()->createToken('test')->plainTextToken)
            ->postJson('/api/v1/utility-accounts', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'account_number',
                'address',
            ]);
    }
}
