<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'tenant_id' => Tenant::factory(),
            'plan_id' => Plan::factory(),
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => null,
            'trial_ends_at' => null,
            'cancelled_at' => null,
            'payment_provider' => 'stripe',
            'payment_provider_subscription_id' => Str::random(20),
            'currency' => 'USD',
        ];
    }

    public function onTrial()
    {
        return $this->state([
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    public function cancelled()
    {
        return $this->state([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
