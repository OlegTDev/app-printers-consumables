<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Consumable\ConsumableCountInstalled;

class ConsumableCountInstalledPolicy
{
    public function store(User $user): bool
    {
        return $user->hasRole(['admin', 'subtract-consumable']);
    }

    public function delete(User $user, ConsumableCountInstalled $installed): bool
    {
        return $user->hasRole('admin') || $installed->id_author === $user->id;
    }
}
