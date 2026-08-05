<?php

namespace Tests\Unit\Services;

use App\Services\CacheKeys;
use PHPUnit\Framework\TestCase;

class CacheKeysTest extends TestCase
{
    public function test_it_get_org_cache_key(): void
    {
        $service = new CacheKeys();
        $userId = 1;
        $this->assertEquals($service->getOrgCacheKey($userId), "user_org_list_{$userId}");
    }

}
