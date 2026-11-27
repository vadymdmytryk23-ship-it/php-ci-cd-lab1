<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UtilityAccount;

class UtilityAccountPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, UtilityAccount $account): bool
    {
        return true;
    }

    public function viewPrivate(User $user, UtilityAccount $account): bool
    {
        return $user->status === 'active' && ($user->id === $account->user_id || $user->role === 'administrator');
    }

    public function create(User $user): bool
    {
        return $user->status === 'active';
    }

    public function update(User $user, UtilityAccount $account): bool
    {
        return $this->create($user) && $user->id === $account->user_id;
    }

    public function delete(User $user, UtilityAccount $account): bool
    {
        return $this->update($user, $account);
    }
}
