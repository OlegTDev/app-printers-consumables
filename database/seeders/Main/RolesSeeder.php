<?php

namespace Database\Seeders\Main;

use App\Models\Auth\Role;
use Illuminate\Database\Seeder;

/**
 * Роли
 */
class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = (require database_path('seeders/Main/data.php'))['roles'];
        foreach ($roles as $name => $description) {
            Role::query()->updateOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}
