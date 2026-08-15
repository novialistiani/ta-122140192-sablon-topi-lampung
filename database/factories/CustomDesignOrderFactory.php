<?php

namespace Database\Factories;

use App\Models\CustomDesignOrder;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomDesignOrderFactory extends Factory
{
    protected $model = CustomDesignOrder::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'product_name' => 'Custom Design',
            'product_price' => 0,
            'cutting_type' => $this->faker->randomElement(['Cutting PVC Flex', 'Printable', 'Direct Transfer Film']),
            'special_materials' => json_encode($this->faker->randomElements(['Foil', 'Glitter', 'Spectrum'], 0)),
            'additional_description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'total_price' => $this->faker->numberBetween(100000, 1000000),
        ];
    }
}
