<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\UtilityService;
use Illuminate\Database\Eloquent\Model;

class CatalogPolicy
{
    public function viewAny(?User $user, Model $model): bool
    {
        return $model instanceof ServiceProvider || $model instanceof UtilityService
            || ($this->active($user) && (! ($model instanceof User) || $this->admin($user)));
    }

    public function view(?User $user, Model $item): bool
    {
        if ($item instanceof ServiceProvider || $item instanceof UtilityService) {
            return true;
        }

        return $this->active($user) && ($this->admin($user) || $this->owns($user, $item));
    }

    public function create(User $user, Model $model): bool
    {
        return $this->admin($user) || ($this->active($user) && ($model instanceof MeterReading || $model instanceof Payment));
    }

    public function update(User $user, Model $item): bool
    {
        return $this->admin($user) || ($item instanceof MeterReading && $this->view($user, $item));
    }

    public function delete(User $user, Model $item): bool
    {
        return $this->update($user, $item);
    }

    private function active(?User $user): bool
    {
        return $user?->status === 'active';
    }

    private function admin(?User $user): bool
    {
        return $this->active($user) && $user->role === 'administrator';
    }

    private function owns(User $user, Model $item): bool
    {
        $owner = match (true) {
            $item instanceof Bill, $item instanceof MeterReading => $item->utilityAccount->user_id,
            $item instanceof Payment => $item->bill->utilityAccount->user_id,
            $item instanceof Receipt => $item->payment->bill->utilityAccount->user_id,
            default => null,
        };

        return $owner === $user->id;
    }
}
