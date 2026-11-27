<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Payment;
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
        UtilityAccount::factory()->count(4)->hasAttached($service)->create();

        DB::enableQueryLog();
        $this->getJson('/api/v1/utility-accounts')->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0.services.0.id', $service->id);
        $selects = collect(DB::getQueryLog())->filter(fn (array $query): bool => str_starts_with($query['query'], 'select'));
        DB::disableQueryLog();

        $this->assertCount(5, $selects);
    }

    public function test_account_and_pivot_rows_are_saved_and_can_be_updated(): void
    {
        $user = User::factory()->create();
        [$first, $second] = UtilityService::factory()->count(2)->create();
        $this->withToken($user->createToken('test')->plainTextToken);
        $id = $this->postJson('/api/v1/utility-accounts', [
            'account_number' => 'UA-DB-15001',
            'address' => 'Київ, вул. Хрещатик, 15',
            'service_ids' => [$first->id, $second->id],
        ])->assertCreated()->assertJsonCount(2, 'data.services')->json('data.id');

        $this->assertDatabaseHas('utility_accounts', ['id' => $id, 'user_id' => $user->id]);
        $this->assertDatabaseHas('utility_account_utility_service', ['utility_account_id' => $id, 'utility_service_id' => $first->id]);
        $this->assertDatabaseHas('utility_account_utility_service', ['utility_account_id' => $id, 'utility_service_id' => $second->id]);
        $this->putJson('/api/v1/utility-accounts/'.$id, [
            'account_number' => 'UA-DB-15001', 'address' => 'Київ, вул. Хрещатик, 15', 'service_ids' => [$second->id],
        ])->assertOk();
        $this->assertDatabaseMissing('utility_account_utility_service', ['utility_account_id' => $id, 'utility_service_id' => $first->id]);
        $this->getJson('/api/v1/utility-accounts/'.$id)->assertOk()->assertJsonPath('data.services.0.id', $second->id);
    }

    public function test_payments_update_debt_and_confirmation_creates_one_receipt(): void
    {
        $bill = Bill::factory()->create(['amount' => 120]);
        $this->withToken($bill->utilityAccount->user->createToken('test')->plainTextToken);
        foreach ([20, 100] as $amount) {
            $payment = $this->postJson('/api/v1/payments', ['bill_id' => $bill->id, 'amount' => $amount, 'method' => 'card'])
                ->assertCreated()->json('data.id');
            $path = '/api/v1/payments/'.$payment.'/confirm';
            $this->postJson($path)->assertOk()->assertJsonPath('data.status', 'paid');
            $this->assertSame($amount === 20 ? 'unpaid' : 'paid', $bill->refresh()->status);
            $this->postJson($path)->assertOk();
            $receipt = Payment::findOrFail($payment)->receipt;
            $this->get('/api/v1/receipts/'.$receipt->id.'/download')->assertDownload($receipt->number.'.txt');
        }
        $this->assertDatabaseCount('receipts', 2);
        $this->postJson('/api/v1/payments', ['bill_id' => $bill->id, 'amount' => 1, 'method' => 'card'])->assertUnprocessable();
    }
}
