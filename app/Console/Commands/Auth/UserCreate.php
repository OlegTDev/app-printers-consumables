<?php

namespace App\Console\Commands\Auth;

use App\Models\Auth\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class UserCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auth-user-create {name} {password} {email} {org_code=99} {fio?} {department?} {post?} {telephone?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создание пользователя';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $password = $this->argument('password');
        $email = $this->argument('email');
        $orgCode = $this->argument('org_code');
        $fio = $this->argument('fio');
        $department = $this->argument('department');
        $post = $this->argument('post');
        $telephone = $this->argument('telephone');

        $user = User::where('name', $name)->orWhere('email', $email)->first();
        if ($user) {
            $this->error("Пользователь с именем $name или почтой $email уже существует.");
            return 1;
        }

        (new User([
            'name' => $name,
            'fio' => $fio,
            'department' => $department,
            'post' => $post,
            'telephone' => $telephone,
            'email' => $email,
            'password' => Hash::make($password),
            'org_code' => $orgCode,
        ]))->save();

        $this->info('Пользователь создан');
        $this->info("Имя пользователя: $name");
        $this->info("Пароль: $password");
        $this->info("Email: $email");
        $this->info("Код организации: $orgCode");
        $this->info("ФИО: $fio");
        $this->info("Отдел: $department");
        $this->info("Должность: $post");
        $this->info("Телефон: $telephone");
        return 0;
    }
}
