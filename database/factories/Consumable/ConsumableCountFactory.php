<?php

namespace Database\Factories\Consumable;

use App\Models\Consumable\Consumable;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Consumable\ConsumableCount>
 */
class ConsumableCountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_consumable' => Consumable::factory(),
            'count' => $this->faker->numberBetween(1, 10),
        ];
    }

    public function withOrganization(Organization $organization): Factory
    {
        return $this->state(function (array $attributes) use($organization) {
            return [
                'id_consumable' => Consumable::factory()->withOrganization($organization),
            ];
        });
    }
}
