<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;
use App\Models\User;
use App\Models\Client;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', '+1 month');
        $deadline = fake()->dateTimeBetween($startDate, '+1 year');
        
        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(2, true),
            'status' => fake()->randomElement(['draft', 'active', 'completed', 'paused']),
            'budget' => fake()->randomFloat(2, 1000, 50000),
            'hourly_rate' => fake()->optional()->randomFloat(2, 200, 1000),
            'start_date' => $startDate->format('Y-m-d'),
            'deadline' => $deadline->format('Y-m-d'),
            'google_calendar_event_id' => fake()->optional()->uuid(),
        ];
    }
}
