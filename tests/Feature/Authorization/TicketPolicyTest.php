<?php

namespace Tests\Feature\Authorization;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, ?string $email = null): User
    {
        return User::factory()->create([
            'role' => $role,
            'email' => $email ?? fake()->unique()->safeEmail(),
            'password' => 'password',
        ]);
    }

    private function makeCategory(): Category
    {
        return Category::factory()->create([
            'name' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(),
        ]);
    }

    private function makeTicket(User $customer, ?User $assignee = null, ?Category $category = null): Ticket
    {
        return Ticket::factory()->create([
            'user_id' => $customer->id,
            'assigned_to' => $assignee?->id,
            'category_id' => $category?->id,
            'status' => 'open',
            'priority' => 'medium',
            'subject' => 'Test ticket',
            'description' => 'Test description',
        ]);
    }

   
    public function test_guest_cannot_access_tickets_endpoints(): void
    {
        $this->getJson('/api/tickets')->assertStatus(401);
    }

    
    public function test_customer_can_only_view_own_ticket(): void
    {
        $customerA = $this->makeUser(User::ROLE_CUSTOMER);
        $customerB = $this->makeUser(User::ROLE_CUSTOMER);

        $ticketA = $this->makeTicket($customerA);
        $ticketB = $this->makeTicket($customerB);

        // Can view own
        $this->actingAs($customerA, 'sanctum')
            ->getJson("/api/tickets/{$ticketA->id}")
            ->assertOk();

        // Cannot view others
        $this->actingAs($customerA, 'sanctum')
            ->getJson("/api/tickets/{$ticketB->id}")
            ->assertStatus(403);
    }

    
    public function test_agent_can_only_view_assigned_ticket(): void
    {
        $agent = $this->makeUser(User::ROLE_AGENT);
        $customer = $this->makeUser(User::ROLE_CUSTOMER);

        $assignedTicket = $this->makeTicket($customer, $agent);
        $unassignedTicket = $this->makeTicket($customer, null);

        // Agent can view assigned
        $this->actingAs($agent, 'sanctum')
            ->getJson("/api/tickets/{$assignedTicket->id}")
            ->assertOk();

        // Agent cannot view unassigned
        $this->actingAs($agent, 'sanctum')
            ->getJson("/api/tickets/{$unassignedTicket->id}")
            ->assertStatus(403);
    }

    
    public function test_agent_can_update_only_assigned_ticket(): void
    {
        $agent = $this->makeUser(User::ROLE_AGENT);
        $customer = $this->makeUser(User::ROLE_CUSTOMER);

        $assignedTicket = $this->makeTicket($customer, $agent);
        $otherTicket = $this->makeTicket($customer, null);

        // update assigned -> OK
        $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/tickets/{$assignedTicket->id}", [
                'status' => 'pending',
                'priority' => 'high',
            ])
            ->assertOk();

        $this->assertDatabaseHas('tickets', [
            'id' => $assignedTicket->id,
            'status' => 'pending',
            'priority' => 'high',
        ]);

        // update not assigned -> FORBIDDEN
        $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/tickets/{$otherTicket->id}", [
                'status' => 'resolved',
            ])
            ->assertStatus(403);
    }

   
    public function test_admin_can_view_any_ticket_and_assign_ticket(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $agent = $this->makeUser(User::ROLE_AGENT);
        $customer = $this->makeUser(User::ROLE_CUSTOMER);

        $ticket = $this->makeTicket($customer, null);

        // Admin can view
        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/tickets/{$ticket->id}")
            ->assertOk();

        // Admin can assign
        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/tickets/{$ticket->id}/assign", [
                'assigned_to' => $agent->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $agent->id,
        ]);
    }
}
