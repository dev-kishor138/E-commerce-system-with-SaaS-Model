<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Domain>
 */
class DomainFactory extends Factory
{
    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'domain' => $this->faker->unique()->domainName,
            'tenant_id' => Tenant::factory(),
            'is_primary' => false,
            'ssl_enabled' => false,
            'type' => 'subdomain',
            'ssl_expires_at' => null,
        ];
    }

    public function primary()
    {
        return $this->state(['is_primary' => true]);
    }

    public function sslEnabled()
    {
        return $this->state([
            'ssl_enabled' => true,
            'ssl_expires_at' => now()->addYear(),
        ]);
    }
}
