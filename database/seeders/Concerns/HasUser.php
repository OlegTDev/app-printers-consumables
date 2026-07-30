<?php

namespace Database\Seeders\Concerns;

use App\Models\Auth\User;

trait HasUser
{
    protected function getUserAdmin(): User
    {
        return $this->getUserByEmail('admin@example.com');
    }

    protected function getUser(): User
    {
        return $this->getUserByEmail('user@example.com');
    }

    protected function getUserByEmail(string $email): User
    {
        return User::query()->whereEmail($email)->firstOrFail();
    }

    protected function getRandomUser(): User
    {
        return User::query()->inRandomOrder()->firstOrFail();
    }

}
