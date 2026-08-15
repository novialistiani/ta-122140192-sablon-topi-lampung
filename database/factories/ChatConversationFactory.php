<?php

namespace Database\Factories;

use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatConversationFactory extends Factory
{
    protected $model = ChatConversation::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'subject' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['open', 'closed']),
        ];
    }
}
