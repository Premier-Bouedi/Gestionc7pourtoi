<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween('-6 months', 'now');
        $dueDate = fake()->dateTimeBetween($issueDate, '+1 month');
        
        $subtotal = fake()->randomFloat(2, 500, 20000);
        $tvaRate = 0.20; // 20% TVA au Maroc généralement
        $tvaAmount = $subtotal * $tvaRate;
        $total = $subtotal + $tvaAmount;

        $status = fake()->randomElement(['draft', 'sent', 'paid', 'overdue', 'cancelled']);
        $paidAt = ($status === 'paid') ? fake()->dateTimeBetween($issueDate, 'now') : null;

        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'project_id' => fake()->optional(0.7)->randomElement([Project::factory()]),
            'invoice_number' => 'INV-' . fake()->unique()->numerify('####-####'),
            'status' => $status,
            'issue_date' => $issueDate->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d'),
            'subtotal' => $subtotal,
            'tva_amount' => $tvaAmount,
            'total' => $total,
            'notes' => fake()->optional()->paragraph(),
            'google_drive_path' => fake()->optional()->url(),
            'paid_at' => $paidAt,
        ];
    }
}
