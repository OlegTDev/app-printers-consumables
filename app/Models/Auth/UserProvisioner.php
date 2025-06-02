<?php

namespace App\Models\Auth;

use App\Models\Auth\User;
use Illuminate\Support\Str;


/**
 * Поиск/создание пользователя в БД
 * Используется при аутентификации и создании нового пользователя
 */
class UserProvisioner
{

    /**
     * Поиск пользователя в БД
     * Если пользователя нет, то создается новый
     * @return User
     */
    public function get(string $username, string $domain, $userAttributes): User
    {
        $user = $this->find($username);

        if (empty($user)) {
            $user = $this->createUser($username, $domain, $userAttributes);
        } else {
            $this->updateUser($user, $userAttributes);
        }
                
        if ($user->isDirty()) {
            $user->save();
        }

        return $user;
    }

    /**
     * Поиск пользователя в БД
     * @param string $username имя пользователя
     * @return User
     */
    public function find(string $username)
    {
        return User::where('name', $username)->first();
    }

    /**
     * Создание нового пользователя
     * @param string $username имя пользователя
     * @param string $domain имя домена
     * @param array $attributes данные пользователя
     * @return User
     */
    public function createUser(string $username, string $domain, $attributes): User
    {
        return new User([
            'name' => $username,
            'email' => $attributes['userPrincipalName'],
            'password' => $this->generateUserPassword(),
            'domain' => $domain,
            'org_code' => User::getOrgCodeFromUsername($username),
            'company' => $attributes['company'],
            'fio' => $attributes['cn'],
            'department' => $attributes['department'],
            'post' => $attributes['title'],
            'telephone' => $attributes['telephoneNumber'],
            'lotus_mail' => $attributes['mail'],
            'members' => $attributes['memberOf'],
        ]);
    }

    /**
     * Обновление данных пользователя
     * @param User $model модель пользователя
     * @param array $attributes данные пользователя
     * @return void
     */
    public function updateUser(User $model, array $attributes): void
    {
        $model->fill([
            'email' => $attributes['userPrincipalName'],
            'company' => $attributes['company'],
            'fio' => $attributes['cn'],
            'department' => $attributes['department'],
            'post' => $attributes['title'],
            'telephone' => $attributes['telephoneNumber'],
            'lotus_mail' => $attributes['mail'],
            'members' => $attributes['memberOf'],
        ]);
    }

    /**
     * Генерация пароля для нового пользователя
     * @return string
     */
    private function generateUserPassword(): string
    {
        return Str::password(16);
    }

}