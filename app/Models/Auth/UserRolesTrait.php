<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Управление ролями пользователя
 */
trait UserRolesTrait
{

    public function hasRole(string|array $role): bool
    {
        return $this->roles()->whereIn('name', (array)$role)->exists();
    }


    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'roles_users', 'id_user', 'id_role');
    }


    public function updateRoles(array|string $roles): void
    {
        $rolesIds = $this->getRolesIdsByNames((array) $roles);
        $this->roles()->sync($rolesIds);
    }

    private function getRolesIdsByNames(array $roles)
    {
        return Role::whereIn('name', $roles)->pluck('id')->toArray();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

}
