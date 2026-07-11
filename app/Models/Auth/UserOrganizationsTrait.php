<?php

namespace App\Models\Auth;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/**
 * Управлениями привязками организаций к пользователю
 */
trait UserOrganizationsTrait
{
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'users_organizations', 'id_user', 'org_code');
    }

    public function hasOrganization(string|array $code): bool
    {
        return $this->organizations()->whereIn('code', (array)$code)->exists();
    }

    public function updateOrganizations(array $organizationsCodes): void
    {
        $this->organizations()->sync($organizationsCodes);
    }

}
