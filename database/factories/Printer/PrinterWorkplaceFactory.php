<?php

namespace Database\Factories\Printer;

use App\Models\Auth\User;
use App\Models\Organization;
use App\Models\Printer\Printer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Printer\PrinterWorkplace>
 */
class PrinterWorkplaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_printer' => Printer::factory(),
            'org_code' => Organization::factory(),
            'location' => (string)$this->faker->randomNumber(3),
            'serial_number' => $this->faker->regexify('([A-Z0-9]{5})'),
            'inventory_number' => (string)$this->faker->unique()->regexify('([A-Z0-9]{20})'),
            'id_author' => User::factory(),
        ];
    }

    public function withOrganization(Organization $organization): Factory
    {
        return $this->state(function (array $attributes) use($organization) {
            return [
                'org_code' => $organization->code,
                'id_printer' => Printer::factory()->withOrganization($organization),
                'id_author' => User::factory()->withOrganization($organization),
            ];
        });
    }
}
