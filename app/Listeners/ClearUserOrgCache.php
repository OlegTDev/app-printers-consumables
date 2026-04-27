<?php

namespace App\Listeners;

use App\Services\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Auth\Events\Login;

class ClearUserOrgCache
{
    public function __construct(private CacheKeys $cacheKeys)
    {
    }

    public function handle(Login $event)
    {
        /** @var \App\Models\Auth\User $user */
        $user = $event->user;
        Cache::forget($this->cacheKeys->getOrgCacheKey($user->id));
    }
}
