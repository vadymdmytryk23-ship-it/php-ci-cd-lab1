<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class UtilityAccountAccessTest extends TestCase
{
    private array $accountData = [
        'account_number' => 'UA-150004',
        'address' => 'м. Київ, вул. Січових Стрільців, 15',
        'provider' => 'Київські енергетичні послуги',
        'service_type' => 'electricity',
    ];

    public function test_accounts_can_be_received(): void
    {
        $this->getJson('/utility-accounts')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(3, 'data');
    }

    public function test_existing_account_can_be_received(): void
    {
        $this->getJson('/utility-accounts/1')
            ->assertOk()
            ->assertJsonPath('data.account_number', 'UA-150001');
    }

    public function test_missing_account_returns_not_found(): void
    {
        $this->getJson('/utility-accounts/999')
            ->assertNotFound()
            ->assertJsonPath('status', 'error');
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
            ->postJson('/utility-accounts', $this->accountData)
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
                'provider',
                'service_type',
            ]);
    }
}
