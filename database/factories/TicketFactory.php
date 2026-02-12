<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        
        $customer = User::factory()->customer()->create();

        return [
            'user_id' => $customer->id,

            
            'category_id' => Category::factory(),

            'assigned_to' => null,

            'subject' => fake()->sentence(6),
            'description' => fake()->paragraph(3),

            'status' => Ticket::STATUS_OPEN,
            'priority' => Ticket::PRIORITY_MEDIUM,
        ];
    }


    public function unassigned(): static
    {
        return $this->state(fn () => ['assigned_to' => null]);
    }

    public function assignedTo(User $agent): static
    {
        return $this->state(fn () => ['assigned_to' => $agent->id]);
    }

    public function forCustomer(User $customer): static
    {
        return $this->state(fn () => ['user_id' => $customer->id]);
    }

    public function withStatus(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function withPriority(string $priority): static
    {
        return $this->state(fn () => ['priority' => $priority]);
    }
}
