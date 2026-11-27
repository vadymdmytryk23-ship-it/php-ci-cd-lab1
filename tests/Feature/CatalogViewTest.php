<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\User;
use App\Models\UtilityAccount;
use App\Models\UtilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_and_detail_display_linked_data(): void
    {
        $user = User::factory()->create(['name' => 'Тестовий споживач']);
        $service = UtilityService::factory()->create(['name' => 'Гаряча вода']);
        $account = UtilityAccount::factory()->for($user)->hasAttached($service)->create();
        $other = UtilityAccount::factory()->create();

        $this->actingAs($user)->get(route('web.utility-accounts.index'))
            ->assertOk()->assertSee($account->account_number)->assertSee('Гаряча вода')
            ->assertSee('Тестовий споживач')->assertDontSee($other->account_number);
        $this->get(route('web.utility-accounts.show', $account))->assertOk()->assertSee('Гаряча вода');
        $this->get(route('web.utility-accounts.edit', $other))->assertForbidden();
        $this->get(route('web.users.index'))->assertForbidden();
    }

    public function test_form_validation_returns_old_input_and_field_errors(): void
    {
        $this->actingAs(User::factory()->create());
        $form = route('web.utility-accounts.create');
        $this->get($form)->assertOk()->assertSee('name="_token"', false);
        $this->from($form)->post(route('web.utility-accounts.store'), ['address' => 'Чернетка адреси'])
            ->assertRedirect($form)->assertSessionHasErrors('account_number')->assertSessionHasInput('address', 'Чернетка адреси');
        $this->get($form)->assertSee('Чернетка адреси')->assertSee('is-invalid');
    }

    public function test_owner_can_create_update_and_delete_an_account_with_services(): void
    {
        $owner = User::factory()->create();
        [$first, $second] = UtilityService::factory()->count(2)->create();
        $data = [
            'account_number' => 'UA-WEB-15001', 'address' => 'Вулиця Шевченка, 15',
            'service_ids' => [$first->id, $second->id],
        ];
        $index = route('web.utility-accounts.index');
        $this->actingAs($owner)->post(route('web.utility-accounts.store'), $data)
            ->assertRedirect($index)->assertSessionHas('success');
        $account = UtilityAccount::where('account_number', 'UA-WEB-15001')->firstOrFail();
        $this->assertDatabaseCount('utility_account_utility_service', 2);
        $this->get(route('web.utility-accounts.edit', $account))
            ->assertOk()->assertSee('UA-WEB-15001')->assertSee('name="_method" value="PUT"', false);
        $this->post(route('web.utility-accounts.update', $account), ['_method' => 'PUT'] + array_replace($data, [
            'address' => 'Вулиця Січових Стрільців, 15', 'service_ids' => [$second->id],
        ]))->assertRedirect($index)->assertSessionHas('success');
        $this->assertSame([$second->id], $account->utilityServices()->get()->modelKeys());
        $this->post(route('web.utility-accounts.destroy', $account), ['_method' => 'DELETE'])
            ->assertRedirect($index)->assertSessionHas('success');
        $this->assertModelMissing($account);
    }

    public function test_admin_can_open_all_catalog_sections_and_create_forms(): void
    {
        $this->seed();
        $this->actingAs(User::where('role', 'administrator')->firstOrFail());
        foreach (array_keys(config('catalog')) as $key) {
            $this->get(route("web.$key.index"))->assertOk();
            $this->get(route("web.$key.create"))->assertOk();
            $id = config("catalog.$key.model")::firstOrFail()->id;
            $this->get(route("web.$key.show", $id))->assertOk();
            $this->get(route("web.$key.edit", $id))->assertStatus($key === 'utility-accounts' ? 403 : 200);
        }
    }

    public function test_customer_can_confirm_payment_and_download_receipt(): void
    {
        $user = User::factory()->create();
        $account = UtilityAccount::factory()->for($user)->create();
        $bill = Bill::factory()->for($account)->create(['amount' => 120]);
        $this->actingAs($user)->get(route('web.bills.show', $bill))->assertOk()->assertSee('Перейти до оплати');
        $this->post(route('web.payments.store'), ['bill_id' => $bill->id, 'amount' => 120, 'method' => 'card'])
            ->assertRedirect(route('web.payments.index'));
        $payment = Payment::firstOrFail();
        $this->post(route('web.payments.confirm', $payment))->assertRedirect(route('web.payments.show', $payment));
        $this->assertSame('paid', $bill->fresh()->status);
        $this->get(route('web.receipts.download', $payment->receipt))->assertDownload($payment->receipt->number.'.txt');
    }

    public function test_login_and_logout_use_session_routes(): void
    {
        $user = User::factory()->create();
        $this->get(route('web.utility-accounts.create'))->assertRedirect(route('login'));
        $this->post(route('web.login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('web.utility-accounts.create'));
        $this->get(route('web.profile'))->assertOk()->assertSee($user->email);
        $this->post(route('web.logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_admin_can_save_and_remove_records_across_the_catalog(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'administrator']));
        $records = [];
        $write = function (string $key, array $data) use (&$records): int {
            $this->post(route("web.$key.store"), $data)->assertRedirect(route("web.$key.index"));
            $item = config("catalog.$key.model")::latest('id')->firstOrFail();
            $this->put(route("web.$key.update", $item), $data)->assertRedirect(route("web.$key.index"));
            $records[$key] = $item;

            return $item->id;
        };
        $write('users', ['name' => 'Споживач', 'email' => 'new@example.test', 'password' => 'demo-password', 'role' => 'customer', 'status' => 'active']);
        $provider = $write('service-providers', ['name' => 'Водоканал', 'edrpou' => '20202020', 'bank_account' => 'UA12345678', 'contact_email' => 'water@example.test']);
        $service = $write('utility-services', ['name' => 'Вода', 'service_provider_id' => $provider, 'unit' => 'м³', 'tariff' => '30.38', 'active_from' => '2026-01-01']);
        $account = $write('utility-accounts', ['account_number' => 'UA-202020', 'address' => 'Вулиця Шевченка, 15', 'service_ids' => [$service]]);
        $bill = $write('bills', ['utility_account_id' => $account, 'utility_service_id' => $service, 'period' => '2026-11', 'amount' => '120.00', 'due_date' => '2026-12-20']);
        $write('meter-readings', ['utility_account_id' => $account, 'previous_value' => '20', 'current_value' => '24']);
        $payment = $write('payments', ['bill_id' => $bill, 'amount' => '120.00', 'method' => 'card']);
        $this->post(route('web.payments.confirm', $payment))->assertRedirect();
        Payment::findOrFail($payment)->receipt()->delete();
        $write('receipts', ['payment_id' => $payment, 'number' => 'RC-RESTORED-15']);

        foreach (array_reverse($records, true) as $key => $item) {
            $this->delete(route("web.$key.destroy", $item))->assertRedirect(route("web.$key.index"));
            $this->assertModelMissing($item);
        }
    }
}
