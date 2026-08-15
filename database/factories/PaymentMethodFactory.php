<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word() . ' Payment',
            'code' => strtoupper($this->faker->unique()->bothify('???-####')),
            'type' => $this->faker->randomElement(['bank_transfer', 'credit_card', 'ewallet', 'qrcode']),
            'is_active' => $this->faker->boolean(),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
