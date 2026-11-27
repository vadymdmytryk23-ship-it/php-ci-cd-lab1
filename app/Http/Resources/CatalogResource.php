<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Bill;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\UtilityService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $fields = match ($this->resource::class) {
            ServiceProvider::class => ['name', 'edrpou', 'contact_email'],
            UtilityService::class => ['service_provider_id', 'name', 'unit', 'tariff', 'active_from'],
            Bill::class => ['utility_account_id', 'utility_service_id', 'period', 'amount', 'due_date', 'status'],
            Payment::class => ['bill_id', 'amount', 'method', 'status', 'paid_at'],
            Receipt::class => ['payment_id', 'number', 'issued_at', 'total'],
            MeterReading::class => ['utility_account_id', 'previous_value', 'current_value', 'submitted_at', 'status'],
            User::class => ['name', 'email', 'role', 'status'],
        };
        $data = ['id' => $this->id];
        foreach ($fields as $field) {
            $value = $this->{$field};
            $data[$field] = match (true) {
                $field === 'active_from' || $field === 'due_date' => $value?->format('Y-m-d'),
                $value instanceof \DateTimeInterface => $value->format(DATE_ATOM),
                default => $value,
            };
        }
        if ($this->resource instanceof Bill && $this->resource->relationLoaded('payments')) {
            $data['debt'] = number_format(app(PaymentService::class)->debt($this->resource), 2, '.', '');
        }

        return $data;
    }
}
