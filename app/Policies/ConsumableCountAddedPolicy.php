<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Consumable\ConsumableCountAdded;

class ConsumableCountAddedPolicy
{

    public function delete(User $user, ConsumableCountAdded $added): bool
    {
        return $user->hasRole('admin') || $added->id_author === $user->id;
    }

}
