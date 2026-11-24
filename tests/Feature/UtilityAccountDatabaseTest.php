<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\User;
use App\Models\UtilityAccount;
use App\Models\UtilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UtilityAccountDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_eager_loads_relations_with_a_fixed_query_count(): void
    {
        $service = UtilityService::factory()->create();
        $accounts = UtilityAccount::factory()->count(4)->hasAttached($service)->create();

        DB::enableQueryLog();
        $this->getJson('/utility-accounts')->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0.user.id', $accounts->first()->user_id)
            ->assertJsonPath('data.0.utility_services.0.service_provider.id', $service->service_provider_id);
        $selects = collect(DB::getQueryLog())->filter(fn (array $query): bool => str_starts_with($query['query'], 'select'));
        DB::disableQueryLog();

        $this->assertCount(4, $selects);
    }

    public function test_account_and_pivot_rows_are_saved_and_can_be_updated(): void
    {
        $user = User::factory()->create();
        [$first, $second] = UtilityService::factory()->count(2)->create();
        $this->withHeader('X-Customer-Token', 'customer-15');
        $id = $this->postJson('/utility-accounts', [
            'user_id' => $user->id,
            'account_number' => 'UA-DB-15001',
            'address' => 'Київ, вул. Хрещатик, 15',
            'service_ids' => [$first->id, $second->id],
        ])->assertCreated()->assertJsonCount(2, 'data.utility_services')->json('data.id');

        $this->assertDatabaseHas('utility_accounts', ['id' => $id, 'user_id' => $user->id]);
        $this->assertDatabaseHas('utility_account_utility_service', ['utility_account_id' => $id, 'utility_service_id' => $first->id]);
        $this->assertDatabaseHas('utility_account_utility_service', ['utility_account_id' => $id, 'utility_service_id' => $second->id]);
        $this->patchJson('/utility-accounts/'.$id, ['service_ids' => [$second->id]])->assertOk();
        $this->assertDatabaseMissing('utility_account_utility_service', ['utility_account_id' => $id, 'utility_service_id' => $first->id]);
        $this->getJson('/utility-accounts/'.$id)->assertOk()->assertJsonPath('data.utility_services.0.id', $second->id);
    }

    public function test_payments_update_debt_and_confirmation_creates_one_receipt(): void
    {
        $bill = Bill::factory()->create(['amount' => 120]);
        $this->withHeader('X-Customer-Token', 'customer-15');
        foreach ([20, 100] as $amount) {
            $payment = $this->postJson('/bills/'.$bill->id.'/payments', ['amount' => $amount, 'method' => 'card'])
                ->assertCreated()->json('data.id');
            $path = '/payments/'.$payment.'/confirm';
            $this->postJson($path)->assertOk()->assertJsonPath('data.bill.status', $amount === 20 ? 'unpaid' : 'paid');
            $receipt = $this->postJson($path)->assertOk()->json('data.receipt');
            $this->get('/receipts/'.$receipt['id'].'/download')->assertDownload($receipt['number'].'.txt');
        }
        $this->assertDatabaseCount('receipts', 2);
        $this->postJson('/bills/'.$bill->id.'/payments', ['amount' => 1, 'method' => 'card'])->assertUnprocessable();
    }
}
