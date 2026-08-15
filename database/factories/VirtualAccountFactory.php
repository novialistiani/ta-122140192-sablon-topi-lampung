<?php

namespace Database\Factories;

use App\Models\VirtualAccount;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class VirtualAccountFactory extends Factory
{
    protected $model = VirtualAccount::class;

    public function definition()
    {
        return [
            'order_id' => Order::factory(),
            'bank_name' => $this->faker->randomElement(['BCA', 'BNI', 'Mandiri', 'CIMB']),
            'account_number' => $this->faker->numerify('############'),
            'account_holder_name' => $this->faker->name(),
            'amount' => $this->faker->numberBetween(50000, 1000000),
            'status' => $this->faker->randomElement(['active', 'expired', 'paid']),
            'expired_at' => $this->faker->dateTime(),
            'paid_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
