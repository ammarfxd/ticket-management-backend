<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoTicketsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $agent = User::where('email', 'agent@example.com')->first();
        $customer = User::where('email', 'customer@example.com')->first();

        if (! $admin || ! $agent || ! $customer) {
            // Pastikan DemoUsersSeeder run dulu
            return;
        }

        $categories = Category::all();
        if ($categories->isEmpty()) {
            return;
        }

        $priorities = ['low', 'medium', 'high', 'urgent'];
        $statuses = ['open', 'pending', 'resolved', 'closed'];

        // Create 10 demo tickets
        for ($i = 0; $i < 10; $i++) {
            $category = $categories->random();

            $assigned = fake()->boolean(70) ? $agent->id : null; // 70% assigned
            $status = $assigned ? fake()->randomElement($statuses) : 'open';

            $ticket = Ticket::create([
                'user_id' => $customer->id,
                'category_id' => $category->id,
                'assigned_to' => $assigned,
                'subject' => fake()->sentence(6),
                'description' => fake()->paragraph(3),
                'priority' => fake()->randomElement($priorities),
                'status' => $status,
            ]);

            // Customer first message
            TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $customer->id,
                'body' => fake()->paragraph(2),
                'is_internal' => false,
            ]);

            // If assigned, add agent reply + optional internal note
            if ($assigned) {
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $agent->id,
                    'body' => fake()->paragraph(2),
                    'is_internal' => false,
                ]);

                if (fake()->boolean(30)) {
                    TicketComment::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $agent->id,
                        'body' => 'Internal note: ' . fake()->sentence(10),
                        'is_internal' => true,
                    ]);
                }
            }

            // Sometimes admin adds a note
            if (fake()->boolean(15)) {
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $admin->id,
                    'body' => 'Admin note: ' . fake()->sentence(10),
                    'is_internal' => true,
                ]);
            }
        }
    }
}
