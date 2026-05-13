<?php

namespace App\Models\Auth;

use App\Models\Auth\User;
use Illuminate\Support\Str;


/**
 * Поиск/создание пользователя в БД
 */
class UserProvisioner
{

    public function get(string $username, string $domain, array $userAttributes): User
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

    public function find(string $username): ?User
    {
        return User::where('name', $username)->first();
    }


    public function createUser(string $username, string $domain, array $attributes): User
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
            'members' => $attributes['memberOf'] ?? [],
        ]);
    }

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

    private function generateUserPassword(): string
    {
        return Str::password(16);
    }

}
