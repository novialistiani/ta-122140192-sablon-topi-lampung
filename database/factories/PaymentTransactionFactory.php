<?php

namespace Database\Factories;

use App\Models\PaymentTransaction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'order_type' => 'regular',
            'order_id' => Order::factory(),
            'payment_method' => $this->faker->randomElement(['va', 'ewallet', 'credit_card']),
            'payment_channel' => $this->faker->randomElement(['bca', 'bni', 'gopay', 'ovo']),
            'amount' => $this->faker->numberBetween(50000, 1000000),
            'status' => $this->faker->randomElement(['pending', 'paid', 'failed', 'expired']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
