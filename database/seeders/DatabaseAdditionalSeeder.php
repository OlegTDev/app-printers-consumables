<?php

namespace Database\Seeders;

use Database\Seeders\Additional\OrdersSeeder;
use Database\Seeders\Additional\OrganizationsSeeder;
use Database\Seeders\Additional\PrintersSeeder;
use Database\Seeders\Additional\UsersSeeder;
use Illuminate\Database\Seeder;

class DatabaseAdditionalSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DatabaseSeeder::class,

            UsersSeeder::class,
            OrganizationsSeeder::class,
            PrintersSeeder::class,
            OrdersSeeder::class,
        ]);
    }
}
