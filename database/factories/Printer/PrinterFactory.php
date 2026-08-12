<?php

namespace Database\Factories\Printer;

use App\Models\Auth\User;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Printer\Printer>
 */
class PrinterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor' => $this->faker->randomElement(['HP', 'Xerox', 'Epson', 'Canon', 'Kyocera']),
            'model' => $this->faker->regexify('Model-([A-Z0-9]{10})'),
            'is_color_print' => $this->faker->boolean(),
            'id_author' => User::factory(),
        ];
    }

    public function withOrganization(Organization $organization): Factory
    {
        return $this->state(function (array $attributes) use($organization) {
            return [
                'id_author' => User::factory()->withOrganization($organization),
            ];
        });
    }
}
