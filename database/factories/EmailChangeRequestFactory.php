<?php

namespace Database\Factories;

use App\Models\EmailChangeRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailChangeRequestFactory extends Factory
{
    protected $model = EmailChangeRequest::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'new_email' => $this->faker->unique()->safeEmail(),
            'token' => $this->faker->unique()->sha256(),
            'expires_at' => now()->addDays(1),
            'confirmed_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
