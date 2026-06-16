<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Client;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_name' => fake()->company(),
            'contact_name' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone_whatsapp' => '+2126' . fake()->numerify('########'),
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement(['Casablanca', 'Rabat', 'Marrakech', 'Tanger', 'Agadir', 'Fès']),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
