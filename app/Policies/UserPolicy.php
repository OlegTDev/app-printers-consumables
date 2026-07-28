<?php

namespace App\Policies;

use App\Models\Auth\User;

class UserPolicy
{
    public function edit(User $user, User $model): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->id === $model->id;
    }
}
