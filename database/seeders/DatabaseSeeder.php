<?php

namespace Database\Seeders;

use Database\Seeders\Main\ManufacturersSeeder;
use Database\Seeders\Main\RolesSeeder;
use Database\Seeders\Main\UsersSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RolesSeeder::class,
            UsersSeeder::class,
            ManufacturersSeeder::class,
        ]);
    }
}
