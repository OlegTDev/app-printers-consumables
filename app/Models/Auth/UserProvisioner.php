<?php

namespace App\Models\Auth;

use App\Models\Auth\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserProvisioner
{

    public function __construct(private string $username, private string $domain, private array $userAttributes = [])
    {
    }

    /**
     * Поиск пользователя в БД
     * Если пользователя нет, то создается новый
     * @return User
     */
    public function get(): User
    {
        $user = $this->findUser($this->username);

        if (empty($user)) {
            $user = $this->createUser($this->username, $this->domain, $this->userAttributes);
        } else {
            $this->updateUser($user, $this->userAttributes);
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
    public function findUser(string $username)
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
    private function createUser(string $username, string $domain, $attributes): User
    {
        return new User([
            'name' => $username,
            'email' => $attributes['userPrincipalName'],
            'password' => $this->generateUserPassword(),
            'domain' => $domain,
            'org_code' => User::getOrgCodeFromUsername($this->username),
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
    private function updateUser(User $model, array $attributes): void
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