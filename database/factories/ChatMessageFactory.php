<?php

namespace Database\Factories;

use App\Models\ChatMessage;
use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatMessageFactory extends Factory
{
    protected $model = ChatMessage::class;

    public function definition()
    {
        return [
            'chat_conversation_id' => ChatConversation::factory(),
            'user_id' => User::factory(),
            'message' => $this->faker->paragraph(),
            'sender_type' => $this->faker->randomElement(['customer', 'admin']),
            'is_admin_reply' => $this->faker->boolean(),
            'is_read_by_user' => $this->faker->boolean(),
            'is_read_by_admin' => $this->faker->boolean(),
        ];
    }
}
