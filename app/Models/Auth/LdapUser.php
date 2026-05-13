<?php

namespace App\Models\Auth;

class LdapUser
{

    public function __construct(
        private Ldap $ldap,
        private UserProvisioner $userProvisioner,
    ) {}

    public function findOrCreate(string $username, string $domain): ?User
    {
        $user = $this->userProvisioner->find($username);
        if (!$user) {
            $ldapUserAttr = $this->ldap->find($username);
            if ($ldapUserAttr) {
                $user = $this->userProvisioner->get($username, $domain, (array)$ldapUserAttr);
            }
        }
        return $user;
    }

}
