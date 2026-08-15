<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-' . strtoupper($this->faker->bothify('???-####')),
            'items' => json_encode([
                [
                    'product_id' => Product::factory(),
                    'quantity' => $this->faker->numberBetween(1, 10),
                    'price' => $this->faker->numberBetween(50000, 500000),
                ]
            ]),
            'subtotal' => $this->faker->numberBetween(50000, 1000000),
            'total' => $this->faker->numberBetween(50000, 1000000),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'payment_status' => $this->faker->randomElement(['unpaid', 'paid', 'failed']),
            'paid_at' => $this->faker->optional()->dateTime(),
            'processing_at' => $this->faker->optional()->dateTime(),
            'completed_at' => $this->faker->optional()->dateTime(),
            'cancelled_at' => $this->faker->optional()->dateTime(),
            'approved_at' => $this->faker->optional()->dateTime(),
            'rejected_at' => $this->faker->optional()->dateTime(),
            'confirmation_deadline' => $this->faker->optional()->dateTime(),
        ];
    }
}
