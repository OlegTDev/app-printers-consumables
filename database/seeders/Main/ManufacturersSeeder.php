<?php

namespace Database\Seeders\Main;

use App\Models\Manufacturer;
use Illuminate\Database\Seeder;

class ManufacturersSeeder extends Seeder
{
    public function run(): void
    {
        $manufacturers = (require database_path('seeders/Main/data.php'))['manufacturers'];

        foreach ($manufacturers as $manufacturer) {
            Manufacturer::updateOrCreate(['name' => $manufacturer]);
        }
    }
}
