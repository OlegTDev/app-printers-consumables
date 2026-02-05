<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Управление ролями пользователя
 */
trait UserRolesTrait
{
    /**
     * Проверка принадлежности роли(ей) $role пользователю
     * @param string|array $role
     * @return bool
     */
    public function hasRole(string|array $role): bool
    {
        return $this->roles()->whereIn('name', (array)$role)->exists();
    }

    /**
     * Роли пользователя
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'roles_users', 'id_user', 'id_role');
    }

    /**
     * Установка ролей $roles пользователю
     * @param array $roles
     */
    public function updateRoles($roles)
    {
        $this->clearRoles();
        $roles = (array)$roles;
        if (in_array('admin', $roles)) {
            $roleModel = Role::query()->where('name', 'admin')->first();
            if ($roleModel) {
                $this->roles()->save($roleModel);
            }
            return;
        }
        foreach($roles as $role) {
            $roleModel = Role::query()->where('name', $role)->first();
            if ($roleModel) {
                $this->roles()->save($roleModel);
            }
        }
    }

    /**
     * Удаление ролей
     */
    private function clearRoles()
    {
        $this->roles()->detach();
    }

    /**
     * Описание ролей
     * @todo добавить в БД
     */
    public static function rolesLabels()
    {
        return [
            'admin' => 'Администратор (по всему контексту)',
            'editor-dictionary' => 'Редактор справочников (по всему контексту)',
            'editor-added' => 'Добавление количества (по контексту) - сотрудник ИТ',
            'editor-dev' => 'Уменьшение количества (по контексту) - ФКУ',
        ];
    }

    public function getRoleNames(): array
    {
        return $this->roles()->pluck('name')->toArray();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

}
