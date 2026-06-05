<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 既存のカテゴリIDからランダムに1つ選ぶ
            'category_id' => Category::inRandomOrder()->value('id'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->numberBetween(1, 3), // 1:男性, 2:女性, 3:その他
            'email' => fake()->safeEmail(),
            'tel' => fake()->numerify('090########'),
            'address' => fake()->address(),
            'building' => fake()->optional()->secondaryAddress(),
            'detail' => fake()->realText(100), // 120桁以内のお問い合わせ詳細
        ];
    }
}
