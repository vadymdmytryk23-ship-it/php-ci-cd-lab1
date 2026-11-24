<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\UtilityAccount;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::firstOrCreate(
            ['email' => 'customer@example.test'],
            ['name' => 'Демо споживач', 'password' => 'customer-15'],
        );
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.test'],
            ['name' => 'Демо адміністратор', 'password' => 'admin-demo-15'],
        );
        $admin->forceFill(['role' => 'administrator'])->save();

        $services = collect();
        foreach ([
            ['Київські енергетичні послуги', '12345678', 'Електроенергія', 'кВт·год', 4.32],
            ['Київводоканал', '23456789', 'Водопостачання', 'м³', 30.38],
            ['Київтеплоенерго', '34567890', 'Опалення', 'Гкал', 1654.41],
        ] as [$name, $code, $serviceName, $unit, $tariff]) {
            $provider = ServiceProvider::firstOrCreate(['edrpou' => $code], ServiceProvider::factory()->raw(['name' => $name, 'edrpou' => $code]));
            $services->push($provider->utilityServices()->firstOrCreate(
                ['name' => $serviceName],
                ['unit' => $unit, 'tariff' => $tariff, 'active_from' => '2026-01-01'],
            ));
        }

        foreach (range(1, 5) as $number) {
            $accountNumber = 'UA-15000'.$number;
            if (UtilityAccount::where('account_number', $accountNumber)->exists()) {
                continue;
            }

            $account = UtilityAccount::factory()->for($customer)->create(['account_number' => $accountNumber]);
            $linked = $services->random(2);
            $account->utilityServices()->sync($linked->pluck('id')->all());
            MeterReading::factory()->for($account)->count(2)->create();
            foreach ($linked as $service) {
                $bill = Bill::factory()->for($account)->for($service)->create();
                if ($number === 5) {
                    $bill->update(['status' => 'paid']);
                    $payment = Payment::factory()->for($bill)->create([
                        'amount' => $bill->amount, 'status' => 'paid', 'paid_at' => now(),
                    ]);
                    Receipt::factory()->for($payment)->create(['total' => $payment->amount]);
                }
            }
        }
    }
}
