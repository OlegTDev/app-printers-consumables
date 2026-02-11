<?php
namespace App\Ldap;

use App\Models\Auth\User as DatabaseUser;

class AttributeHandler
{
    public function handle(\LdapRecord\Models\ActiveDirectory\User $ldapUser, DatabaseUser $databaseUser)
    {
        $databaseUser->org_code = $this->getOrgCodeBySAMAccountName($ldapUser->getFirstAttribute('sAMAccountName'));
    }

    private function getOrgCodeBySAMAccountName(string $sAMAccountName): string
    {
        if (preg_match('/^n?\d{4}/i', $sAMAccountName, $matches) && isset($matches[0])) {
            return $matches[0];
        }
        return '0000';
    }
}
