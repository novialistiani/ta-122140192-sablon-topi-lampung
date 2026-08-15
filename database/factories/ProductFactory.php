<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(['Topi Baseball', 'Topi Snapback', 'Topi Kustom']),
            'subcategory' => fake()->randomElement(['Pria', 'Wanita', 'Anak']),
            'price' => fake()->randomFloat(2, 50000, 200000),
            'original_price' => fake()->randomFloat(2, 60000, 250000),
            'image' => 'products/default.jpg',
            'stock' => fake()->numberBetween(0, 100),
            'is_featured' => fake()->boolean(20),
            'is_active' => true,
            'custom_design_allowed' => fake()->boolean(50),
        ];
    }
}
