<?php

namespace App\Models\Auth;

use LdapRecord\Models\ActiveDirectory\User;

class LdapFinder
{

    public function __construct(
        private User $user,
    ) {}

    public function query(string $username): ?User
    {
        return $this->user->where('samaccountname', '=', $username)->first();
    }

}
