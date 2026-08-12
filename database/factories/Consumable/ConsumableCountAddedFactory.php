<?php

namespace Database\Factories\Consumable;

use App\Models\Auth\User;
use App\Models\Consumable\ConsumableCount;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Consumable\ConsumableCountAdded>
 */
class ConsumableCountAddedFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_consumable_count' => ConsumableCount::factory(),
            'count' => $this->faker->numberBetween(1, 10),
            'id_author' => User::factory(),
        ];
    }

    public function withOrganization(Organization $organization): Factory
    {
        return $this->state(function (array $attributes) use($organization) {
            return [
                'id_author' => User::factory()->withOrganization($organization),
                'id_consumable_count' => ConsumableCount::factory()->withOrganization($organization),
            ];
        });
    }
}
