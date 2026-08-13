<?php

namespace App\Policies;

use App\Models\Auth\User;
use App\Models\Consumable\ConsumableCount;

class ConsumableCountPolicy
{
    public function show(User $user, ConsumableCount $consumableCount): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        $orgCodes = $consumableCount->organizations->pluck('code')->all();
        $orgCode = $user->org_code;
        return \in_array($orgCode, $orgCodes);
    }
}
