<?php

namespace Database\Seeders\Main;

use Database\Seeders\Concerns\HasRegionCode;
use App\Models\Auth\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Создание тестовых пользователей
 */
class UsersSeeder extends Seeder
{
    use HasRegionCode;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('secret');
        $users = (require database_path('seeders/Main/data.php'))['users'];

        foreach ($users as $user) {
            $userModel = User::query()->updateOrCreate(
                attributes: ['email' => $user['email']],
                values: [
                    'name' => $user['name'],
                    'password' => $password,
                    'org_code' => $user['org_code'] ?? '',
                    'company' => $user['company'] ?? null,
                    'fio' => $user['fio'] ?? null,
                    'department' => $user['department'] ?? null,
                    'post' => $user['post'] ?? null,
                    'telephone' => $user['telephone'] ?? null,
                ],
            );
            $userModel->updateRoles($user['roles']);
        }
    }

}
