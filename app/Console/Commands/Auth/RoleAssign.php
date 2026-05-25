<?php

namespace App\Console\Commands\Auth;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Console\Command;

class RoleAssign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auth-role-assign {username} {role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $roleName = $this->argument('role');

        $user = User::where('name', $username)->first();
        if (!$user) {
            $this->error("Пользователь $username не найден");
            return 1;
        }

        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error("Роль $roleName не найдена");
            return 1;
        }

        if ($user->roles()->where('name', $roleName)->exists()) {
            $this->info("Пользователь $username уже имеет роль {$role->name}");
            return 0;
        }

        $user->roles()->attach($role);
        $this->info("Роль {$role->name} успешно присвоена пользователю $username");
        return 0;
    }
}
