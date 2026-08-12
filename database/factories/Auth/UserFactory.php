<?php

namespace Database\Factories\Auth;

use App\Models\Auth\Role;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Auth\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'org_code' => Organization::factory(),
        ];
    }

    public function withRoleAdmin(): Factory
    {
        return $this->withRole('admin');
    }

    public function withRole(string $role): Factory
    {
        return $this->withRoles([$role]);
    }

    public function withRoles(array $roles): Factory
    {
        return $this->afterCreating(function (\App\Models\Auth\User $user) use ($roles) {
            $roleIds = [];
            foreach ($roles as $role) {
                $roleModel = Role::where('name', $role)->first() ?? Role::factory()->create(['name' => $role]);
                $roleIds[] = $roleModel->id;
            }
            $user->roles()->syncWithoutDetaching($roleIds);
        });
    }

    public function withOrganization(Organization $organization): Factory
    {
        return $this->state(function (array $attributes) use($organization) {
            return [
                'org_code' => $organization,
            ];
        });
    }
}
