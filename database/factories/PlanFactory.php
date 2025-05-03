<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'name' => $this->faker->unique()->word . ' Plan',
            'price' => $this->faker->randomFloat(2, 10, 100),
            'currency' => 'USD',
            'features' => ['unlimited_products', 'priority_support'],
            'billing_cycle' => $this->faker->randomElement(['monthly', 'yearly']),
            'status' => 'active',
        ];
    }

    public function yearly()
    {
        return $this->state(['billing_cycle' => 'yearly']);
    }

    public function inactive()
    {
        return $this->state(['status' => 'inactive']);
    }
}
