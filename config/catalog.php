<?php

use App\Models\Bill;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\UtilityAccount;
use App\Models\UtilityService;

return [
    'utility-accounts' => [
        'title' => 'Особові рахунки',
        'model' => UtilityAccount::class,
        'relations' => ['user', 'utilityServices.serviceProvider'],
        'owner' => null,
        'fields' => [
            'account_number' => ['label' => 'Номер рахунку', 'type' => 'text', 'rules' => 'required|string|min:6|max:30', 'options' => 'unique', 'display' => null],
            'address' => ['label' => 'Адреса', 'type' => 'text', 'rules' => 'required|string|min:5|max:255', 'options' => null, 'display' => null],
            'user_id' => ['label' => 'Споживач', 'type' => 'select', 'rules' => 'required|integer|exists:users,id', 'options' => 'users', 'display' => 'user.name'],
            'service_ids' => ['label' => 'Комунальні послуги', 'type' => 'checks', 'rules' => 'array|list', 'options' => 'utility-services', 'display' => 'utilityServices'],
        ],
    ],
    'service-providers' => [
        'title' => 'Постачальники',
        'model' => ServiceProvider::class,
        'relations' => [],
        'owner' => null,
        'fields' => [
            'name' => ['label' => 'Назва', 'type' => 'text', 'rules' => 'required|string|max:255', 'options' => 'unique', 'display' => null],
            'edrpou' => ['label' => 'Код ЄДРПОУ', 'type' => 'text', 'rules' => 'required|digits:8', 'options' => 'unique', 'display' => null],
            'bank_account' => ['label' => 'Банківський рахунок', 'type' => 'text', 'rules' => 'required|string|max:34', 'options' => null, 'display' => null],
            'contact_email' => ['label' => 'Електронна пошта', 'type' => 'email', 'rules' => 'required|email|max:255', 'options' => null, 'display' => null],
        ],
    ],
    'utility-services' => [
        'title' => 'Послуги й тарифи',
        'model' => UtilityService::class,
        'relations' => ['serviceProvider'],
        'owner' => null,
        'fields' => [
            'name' => ['label' => 'Назва послуги', 'type' => 'text', 'rules' => 'required|string|max:255', 'options' => null, 'display' => null],
            'service_provider_id' => ['label' => 'Постачальник', 'type' => 'select', 'rules' => 'required|integer|exists:service_providers,id', 'options' => 'service-providers', 'display' => 'serviceProvider.name'],
            'unit' => ['label' => 'Одиниця вимірювання', 'type' => 'text', 'rules' => 'required|string|max:32', 'options' => null, 'display' => null],
            'tariff' => ['label' => 'Тариф, грн', 'type' => 'number', 'rules' => 'required|numeric|min:0|max:99999999.99|decimal:0,2', 'options' => null, 'display' => null],
            'active_from' => ['label' => 'Діє з', 'type' => 'date', 'rules' => 'required|date_format:Y-m-d', 'options' => null, 'display' => null],
        ],
    ],
    'bills' => [
        'title' => 'Нарахування',
        'model' => Bill::class,
        'relations' => ['utilityAccount', 'utilityService', 'payments'],
        'owner' => 'utilityAccount',
        'fields' => [
            'utility_account_id' => ['label' => 'Особовий рахунок', 'type' => 'select', 'rules' => 'required|integer|exists:utility_accounts,id', 'options' => 'utility-accounts', 'display' => 'utilityAccount.account_number'],
            'utility_service_id' => ['label' => 'Послуга', 'type' => 'select', 'rules' => 'required|integer|exists:utility_services,id', 'options' => 'utility-services', 'display' => 'utilityService.name'],
            'period' => ['label' => 'Розрахунковий місяць', 'type' => 'month', 'rules' => 'required|date_format:Y-m', 'options' => null, 'display' => null],
            'amount' => ['label' => 'Сума, грн', 'type' => 'number', 'rules' => 'required|numeric|min:0.01|max:99999999.99|decimal:0,2', 'options' => null, 'display' => null],
            'due_date' => ['label' => 'Сплатити до', 'type' => 'date', 'rules' => 'required|date_format:Y-m-d', 'options' => null, 'display' => null],
        ],
    ],
    'meter-readings' => [
        'title' => 'Показники',
        'model' => MeterReading::class,
        'relations' => ['utilityAccount'],
        'owner' => 'utilityAccount',
        'fields' => [
            'utility_account_id' => ['label' => 'Особовий рахунок', 'type' => 'select', 'rules' => 'required|integer|exists:utility_accounts,id', 'options' => 'utility-accounts', 'display' => 'utilityAccount.account_number'],
            'previous_value' => ['label' => 'Попередній показник', 'type' => 'number', 'rules' => 'required|numeric|min:0|max:999999999.999|decimal:0,3', 'options' => null, 'display' => null],
            'current_value' => ['label' => 'Поточний показник', 'type' => 'number', 'rules' => 'required|numeric|gte:previous_value|max:999999999.999|decimal:0,3', 'options' => null, 'display' => null],
        ],
    ],
    'payments' => [
        'title' => 'Платежі',
        'model' => Payment::class,
        'relations' => ['bill.utilityAccount', 'receipt'],
        'owner' => 'bill.utilityAccount',
        'fields' => [
            'bill_id' => ['label' => 'Нарахування', 'type' => 'select', 'rules' => 'required|integer|exists:bills,id', 'options' => 'bills', 'display' => 'bill.period'],
            'amount' => ['label' => 'Сума платежу, грн', 'type' => 'number', 'rules' => 'required|numeric|min:0.01|max:99999999.99|decimal:0,2', 'options' => null, 'display' => null],
            'method' => ['label' => 'Спосіб оплати', 'type' => 'select', 'rules' => 'required|in:card,payment_service', 'options' => ['card' => 'Картка', 'payment_service' => 'Платіжний сервіс'], 'display' => null],
        ],
    ],
    'receipts' => [
        'title' => 'Квитанції',
        'model' => Receipt::class,
        'relations' => ['payment.bill.utilityAccount'],
        'owner' => 'payment.bill.utilityAccount',
        'fields' => [
            'payment_id' => ['label' => 'Підтверджений платіж', 'type' => 'select', 'rules' => 'required|integer|exists:payments,id', 'options' => 'payments', 'display' => 'payment.id'],
            'number' => ['label' => 'Номер квитанції', 'type' => 'text', 'rules' => 'required|string|max:255', 'options' => 'unique', 'display' => null],
        ],
    ],
    'users' => [
        'title' => 'Користувачі',
        'model' => User::class,
        'relations' => [],
        'owner' => null,
        'fields' => [
            'name' => ['label' => 'Ім’я', 'type' => 'text', 'rules' => 'required|string|max:255', 'options' => null, 'display' => null],
            'email' => ['label' => 'Електронна пошта', 'type' => 'email', 'rules' => 'required|email|max:255', 'options' => 'unique', 'display' => null],
            'password' => ['label' => 'Пароль', 'type' => 'password', 'rules' => 'required|string|min:8|max:72', 'options' => null, 'display' => null],
            'status' => ['label' => 'Стан', 'type' => 'select', 'rules' => 'required|in:active,blocked', 'options' => ['active' => 'Активний', 'blocked' => 'Заблокований'], 'display' => null],
            'role' => ['label' => 'Роль', 'type' => 'select', 'rules' => 'required|in:customer,administrator', 'options' => ['customer' => 'Споживач', 'administrator' => 'Адміністратор'], 'display' => null],
        ],
    ],
];
