<?php

namespace Database\Seeders\Additional;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationsSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = (require database_path('seeders/Additional/data.php'))['organizations'];

        foreach ($organizations as $organization) {
            Organization::updateOrCreate(
                attributes: ['code' => $organization['code']],
                values: [
                    'name' => $organization['name'],
                    'parent' => $organization['parent'],
                ],
            );
        }
    }
}
