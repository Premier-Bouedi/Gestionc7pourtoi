<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Expense;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['hosting', 'tools', 'transport', 'office', 'communication', 'other'];
        
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'category' => fake()->randomElement($categories),
            'expense_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'receipt_image_path' => fake()->optional(0.6)->imageUrl(),
            'ai_analysis_raw' => fake()->optional(0.3)->text(),
            'is_deductible' => fake()->boolean(80), // 80% de chances d'être déductible
        ];
    }
}
