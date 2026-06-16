<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;
    
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'phone_whatsapp' => '+2126' . fake()->numerify('########'),
            'business_name' => fake()->company(),
            'activity_sector' => fake()->randomElement(['Développement Web', 'Design', 'Consulting', 'Marketing', 'E-commerce']),
            'currency' => 'MAD',
            'tva_rate' => 20.00,
            'telegram_chat_id' => fake()->optional()->numerify('#########'),
            'ai_config' => null,
        ];
    }
}
