<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement(['create', 'update', 'delete', 'view']),
            'model' => $this->faker->randomElement(['Order', 'Product', 'User', 'Payment']),
            'model_id' => $this->faker->numberBetween(1, 100),
            'description' => $this->faker->sentence(),
            'changes' => json_encode(['old' => 'value', 'new' => 'value']),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }
}
