<?php
namespace App\Ldap;

use App\Models\Auth\User as DatabaseUser;

class AttributeHandler
{
    public function handle(\LdapRecord\Models\ActiveDirectory\User $ldapUser, DatabaseUser $databaseUser)
    {
        $code = $databaseUser->getFirstAvailableOrganization($this->getOrgCodeBySAMAccountName($ldapUser->getFirstAttribute('sAMAccountName')));
        $databaseUser->org_code = $code;
    }

    private function getOrgCodeBySAMAccountName(string $sAMAccountName): string
    {
        if (preg_match('/^n?\d{4}/i', $sAMAccountName, $matches) && isset($matches[0])) {
            return $matches[0];
        }
        return '0000';
    }
}
