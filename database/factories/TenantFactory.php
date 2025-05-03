<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'name' => $this->faker->unique()->company,
            'domain' => $this->faker->unique()->domainName,
            'status' => $this->faker->randomElement(['active', 'inactive', 'suspended']),
            'owner_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'created_by' => User::factory(),
            'settings' => [
                'theme' => $this->faker->randomElement(['light', 'dark']),
                'language' => $this->faker->randomElement(['en', 'bn', 'es']),
                'timezone' => $this->faker->timezone,
            ],
        ];
    }

    /**
     * Configure the model factory with no domain.
     */
    public function withoutDomain()
    {
        return $this->state(function (array $attributes) {
            return [
                'domain' => null,
            ];
        });
    }

    /**
     * Configure the model factory with suspended status.
     */
    public function suspended()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'suspended',
            ];
        });
    }
}
