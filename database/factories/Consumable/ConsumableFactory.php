<?php

namespace Database\Factories\Consumable;

use App\Models\Auth\User;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Consumable\Consumable>
 */
class ConsumableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $items = [
            'type' => $this->faker->randomElement(ConsumableTypesEnum::names()),
            'name' => $this->faker->unique()->regexify('Model-([A-Z0-9]{20})'),
            'id_author' => User::factory(),
        ];
        if ($items['type'] == ConsumableTypesEnum::cartridge->name) {
            $items['color'] = $this->faker->randomElement(array_keys(CartridgeColors::get()));
        } else {
            $items['description'] = $this->faker->text(40);
        }
        return $items;
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
