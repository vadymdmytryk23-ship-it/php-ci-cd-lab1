<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\LogUtilityAccountCreated;
use App\Models\User;
use App\Models\UtilityAccount;
use App\Models\UtilityService;
use App\Services\CatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class UtilityAccountCrudRefactoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_and_api_save_the_same_owner_and_services_and_dispatch_after_commit(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $services = UtilityService::factory()->count(2)->create()->modelKeys();
        $data = ['address' => 'Вулиця Шевченка, 15', 'service_ids' => $services];

        $this->actingAs($user, 'web')
            ->post(route('web.utility-accounts.store'), $data + ['account_number' => 'UA-WEB-0901'])
            ->assertRedirect(route('web.utility-accounts.index'));
        $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson('/api/v1/utility-accounts', $data + ['account_number' => 'UA-API-0901'])
            ->assertCreated()->assertJsonPath('data.owner_id', $user->id)->assertJsonCount(2, 'data.services');

        $accounts = UtilityAccount::with('utilityServices')->orderBy('id')->get();
        $this->assertCount(2, $accounts);
        foreach ($accounts as $account) {
            $this->assertSame($user->id, $account->user_id);
            $this->assertSame($data['address'], $account->address);
            $this->assertSame($services, $account->utilityServices->modelKeys());
            Queue::assertPushed(LogUtilityAccountCreated::class, fn ($job) => $job->accountId === $account->id
                && $job->ownerId === $user->id && $job->afterCommit === true);
        }
        Queue::assertPushed(LogUtilityAccountCreated::class, 2);
    }

    public function test_rollback_of_transaction_leaves_no_account_links_or_job(): void
    {
        config(['queue.default' => 'database']);
        $user = User::factory()->create();
        $service = UtilityService::factory()->create();

        try {
            DB::transaction(function () use ($user, $service): void {
                app(CatalogService::class)->create($user, 'utility-accounts', [
                    'account_number' => 'UA-ROLLBACK-09',
                    'address' => 'Вулиця Шевченка, 15',
                    'service_ids' => [$service->id],
                ]);
                throw new RuntimeException('Intentional rollback');
            });
            $this->fail('Expected rollback');
        } catch (RuntimeException $exception) {
            $this->assertSame('Intentional rollback', $exception->getMessage());
        }

        $this->assertDatabaseCount('utility_accounts', 0);
        $this->assertDatabaseCount('utility_account_utility_service', 0);
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_invalid_account_is_rejected_before_dispatching_a_job(): void
    {
        Queue::fake();
        $token = User::factory()->create()->createToken('test')->plainTextToken;
        $this->withToken($token)->postJson('/api/v1/utility-accounts', [
            'account_number' => 'UA-INVALID-09', 'address' => 'Вулиця Шевченка, 15', 'service_ids' => [9999],
        ])->assertUnprocessable()->assertJsonValidationErrors('service_ids.0');
        $this->assertDatabaseCount('utility_accounts', 0);
        Queue::assertNothingPushed();
    }

    public function test_sentry_demo_is_unavailable_outside_local_environment(): void
    {
        config(['app.sentry_demo_enabled' => true]);
        $this->post(route('web.sentry-demo'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->post(route('web.sentry-demo'))->assertNotFound();
    }
}
