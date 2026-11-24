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
        $this->getJson('/utility-accounts')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(3, 'data');
    }

    public function test_existing_account_can_be_received(): void
    {
        $account = UtilityAccount::factory()->create(['account_number' => 'UA-150001']);
        $this->getJson('/utility-accounts/'.$account->id)
            ->assertOk()
            ->assertJsonPath('data.account_number', 'UA-150001');
    }

    public function test_missing_account_returns_not_found(): void
    {
        $this->getJson('/utility-accounts/999')
            ->assertNotFound();
    }

    public function test_request_without_customer_token_is_blocked(): void
    {
        $this->postJson('/utility-accounts', $this->accountData)
            ->assertForbidden()
            ->assertJsonPath('status', 'access_denied');
    }

    public function test_request_with_valid_token_creates_account(): void
    {
        $this->withHeader('X-Customer-Token', 'customer-15')
            ->postJson('/utility-accounts', $this->accountData + [
                'user_id' => User::factory()->create()->id,
                'service_ids' => [UtilityService::factory()->create()->id],
            ])
            ->assertCreated()
            ->assertHeader('X-Security-Policy', 'Enforced')
            ->assertJsonPath('status', 'created')
            ->assertJsonPath('data.account_number', 'UA-150004');
    }

    public function test_invalid_data_returns_validation_error(): void
    {
        $this->withHeader('X-Customer-Token', 'customer-15')
            ->postJson('/utility-accounts', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'account_number',
                'address',
                'user_id',
                'service_ids',
            ]);
    }
}
