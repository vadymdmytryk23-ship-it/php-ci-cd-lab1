<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\User;
use App\Models\UtilityAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UtilityAccountApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_endpoints_cannot_bypass_the_versioned_api(): void
    {
        $this->withHeader('X-Customer-Token', 'customer-15')->postJson('/utility-accounts')->assertNotFound();
        $this->postJson('/api/v1/utility-accounts')->assertUnauthorized();
        $this->actingAs(User::factory()->create(), 'web')->postJson('/api/v1/utility-accounts')->assertUnauthorized();
    }

    public function test_foreign_account_cannot_be_edited_or_deleted_through_api_or_web(): void
    {
        $account = UtilityAccount::factory()->create();
        $other = User::factory()->create();
        $this->withToken($other->createToken('test')->plainTextToken);
        $path = '/api/v1/utility-accounts/'.$account->id;
        $this->getJson($path)->assertOk()->assertJsonMissingPath('data.owner_id')->assertJsonMissingPath('data.address');
        $this->putJson($path, [])->assertForbidden();
        $this->deleteJson($path)->assertForbidden();
        $this->assertModelExists($account);
        $this->actingAs($other, 'web')->get(route('web.utility-accounts.edit', $account))->assertForbidden();
    }

    public function test_guest_cannot_change_data_and_can_only_see_public_catalog_fields(): void
    {
        $account = UtilityAccount::factory()->create();
        $this->get(route('web.utility-accounts.index'))->assertOk()->assertSee('Приховано')
            ->assertDontSee($account->account_number)->assertDontSee('Редагувати');
        $this->get(route('web.utility-accounts.create'))->assertRedirect(route('login'));
        $this->getJson('/api/v1/bills')->assertUnauthorized();
    }

    public function test_registration_issues_a_token_and_logout_revokes_only_current_token(): void
    {
        $input = ['name' => 'API Client', 'email' => 'new-api@example.test',
            'password' => 'LongDemoPassword15', 'password_confirmation' => 'LongDemoPassword15', 'role' => 'administrator'];
        $response = $this->postJson('/api/v1/register', $input)->assertCreated()
            ->assertJsonPath('user.role', 'customer')->assertJsonMissingPath('user.password');
        $token = $response->json('token');
        $user = User::where('email', $input['email'])->firstOrFail();
        [$id, $secret] = explode('|', $token, 2);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $id, 'token' => hash('sha256', $secret)]);
        $second = $user->createToken('second')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/profile')->assertOk()->assertJsonPath('data.id', $user->id);
        $this->postJson('/api/v1/logout')->assertNoContent();
        Auth::forgetGuards();
        $this->getJson('/api/v1/profile')->assertUnauthorized();
        Auth::forgetGuards();
        $this->withToken($second)->getJson('/api/v1/profile')->assertOk();
    }

    public function test_customer_cannot_assign_another_owner_or_access_foreign_bills(): void
    {
        $otherBill = Bill::factory()->create();
        $user = User::factory()->create();
        $this->withToken($user->createToken('test')->plainTextToken);
        $this->postJson('/api/v1/utility-accounts', ['account_number' => 'UA-USER-001', 'address' => 'Вулиця Шевченка, 15', 'user_id' => $otherBill->utilityAccount->user_id])
            ->assertUnprocessable()->assertJsonValidationErrors('user_id');
        $this->getJson('/api/v1/bills')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/bills/'.$otherBill->id)->assertForbidden();
        $this->deleteJson('/api/v1/bills/'.$otherBill->id)->assertForbidden();
        $this->getJson('/api/v1/users')->assertForbidden();
    }

    public function test_blocked_user_is_rejected_with_an_existing_token(): void
    {
        $user = User::factory()->create(['status' => 'blocked']);
        $this->withToken($user->createToken('test')->plainTextToken)->getJson('/api/v1/profile')->assertForbidden();
        $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'password'])->assertUnprocessable();
    }
}
