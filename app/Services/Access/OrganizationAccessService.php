<?php

namespace App\Services\Access;

use Cache;
use App\Services\CacheKeys;
use App\Services\Query\OrganizationQueryService;

class OrganizationAccessService
{
    public function __construct(private CacheKeys $cacheKeys, private OrganizationQueryService $organizationQueryService)
    {
    }

    public function isAvailableByOrgCode(string $orgCode, bool $isAdmin, int $userId): bool
    {
        return \in_array($orgCode, $this->getUserAvailableCodes($isAdmin, $userId));
    }

    public function getUserAvailableCodes(bool $isAdmin, int $userId): array
    {
        return Cache::remember(
            $this->cacheKeys->getOrgCacheKey($userId),
            $this->cacheKeys->getOrgCacheTTL(),
            fn () => $this->organizationQueryService->getUserOrganizationsCodes($isAdmin, $userId),
        );
    }

    public function getUserAvailableFirstCode(bool $isAdmin, int $userId): ?string
    {
        $codes = $this->getUserAvailableCodes($isAdmin, $userId);
        return $codes[0] ?? null;
    }

}
