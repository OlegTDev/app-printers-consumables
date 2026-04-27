<?php

declare(strict_types=1);

namespace App\Services\Query;

use App\Models\Auth\User;
use Log;

class UserQueryService
{
    public function changeUserOrganization(int $userId, string $orgCode): bool
    {
        $user = User::find($userId);
        if ($user) {
            return $user->update(['org_code' => $orgCode]);
        } else {
            Log::error('User not found');
        }
        return false;
    }
}
