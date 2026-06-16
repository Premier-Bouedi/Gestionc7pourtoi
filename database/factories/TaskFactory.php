<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Task;
use App\Models\Project;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $assignedDate = fake()->optional(0.8)->dateTimeBetween('-1 month', '+1 month');
        $dueDate = $assignedDate ? fake()->optional(0.9)->dateTimeBetween($assignedDate, '+2 months') : null;

        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['todo', 'in_progress', 'review', 'done']),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'time_spent_seconds' => fake()->numberBetween(0, 36000), // Jusqu'à 10h
            'assigned_date' => $assignedDate ? $assignedDate->format('Y-m-d') : null,
            'due_date' => $dueDate ? $dueDate->format('Y-m-d') : null,
        ];
    }
}
