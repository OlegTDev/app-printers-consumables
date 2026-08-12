<?php

namespace Database\Factories\Order;

use App\Models\Auth\User;
use App\Models\Order\OrderStatusEnum;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Order\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'org_code' => Organization::factory(),
            'status' => $this->faker->randomElement(OrderStatusEnum::values()),
            'comment' => $this->faker->text(),
            'quantity' => random_int(1, 5),
            'requested_by' => static fn() => User::factory()->create(),
            'service_request_number' => $this->faker->regexify('([A-Z0-9]{5})'),
            'service_request_date' => $this->faker->dateTimeInInterval('-1 months', 'today'),
        ];
    }
}
