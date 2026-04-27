<?php

namespace App\Services;

class CacheKeys
{
    public function getOrgCacheKey(string $userId): string
    {
        return "user_org_list_{$userId}";
    }

    public function getOrgCacheTTL(): int
    {
        return now()->addWeek()->timestamp;
    }
}
