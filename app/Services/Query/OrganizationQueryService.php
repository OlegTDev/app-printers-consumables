<?php

namespace App\Services\Query;

use App\Models\Organization;

class OrganizationQueryService
{

    public function getUserOrganizationsCodes(bool $isAdmin, int $userId): array
    {
        $query = Organization::query();
        if (!$isAdmin) {
            $query->join('users_organizations', 'users_organizations.org_code', '=', 'organizations.code')
                ->where('users_organizations.id_user', $userId);
        }
        return $query->pluck('code')->toArray();
    }

    public function getOrganizationsByCodes(array $availableCodes): array
    {
        return Organization::query()
            ->whereIn('code', $availableCodes)
            ->get()
            ->toArray();
    }

}
