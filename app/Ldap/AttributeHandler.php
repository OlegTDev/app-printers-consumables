<?php
namespace App\Ldap;

use App\Models\Auth\User as DatabaseUser;
use App\Services\Access\OrganizationAccessService;

class AttributeHandler
{
    public function __construct(private OrganizationAccessService $organizationAccessService)
    {}

    public function handle(\LdapRecord\Models\ActiveDirectory\User $ldapUser, DatabaseUser $databaseUser)
    {
        if ($databaseUser->org_code) {
            $orgCodeByName = $databaseUser->org_code;
        } else {
            $orgCodeByName = $this->getOrgCodeBySAMAccountName($ldapUser->getFirstAttribute('sAMAccountName'));
        }
        if (!$this->organizationAccessService->isAvailableByOrgCode($orgCodeByName, $databaseUser->isAdmin(), $databaseUser->id)) {
            $orgCodeByName = $this->organizationAccessService->getUserAvailableFirstCode($databaseUser->isAdmin(), $databaseUser->id);
        }
        $databaseUser->org_code = $orgCodeByName ?? '';
    }

    private function getOrgCodeBySAMAccountName(string $sAMAccountName): ?string
    {
        if (preg_match('/^n?\d{4}/i', $sAMAccountName, $matches)) {
            return $matches[0];
        }
        return null;
    }
}
