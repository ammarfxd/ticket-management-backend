<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
     /**
     * Admin can do everything (policy filter).
     */
    public function before(User $user, string $ability): bool|null  
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /** 
    * List of tickets: 
    * - customer: allow (controller will scope to own) 
    * - agent: allow (controller will scope to assigned) 
    */  
    public function viewAny(User $user): bool
    {
        return $user->isCustomer() || $user->isAgent();
    }

     /**
     * View ticket:
     * - customer: only own ticket
     * - agent: only assigned ticket
     */
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isCustomer()) {
            return (int) $ticket->user_id === (int) $user->id;
        }

        if ($user->isAgent()) {
            return (int) $ticket->assigned_to === (int) $user->id;
        }

        return false;
    }

    /**
     * Create ticket: customer only.
     */
    public function create(User $user): bool
    {
        return $user->isCustomer();
    }

     /**
     * Update ticket status/priority:
     * - agent: assigned only
     * - customer: usually cannot be updated (except to allow editing subject/desc)
     */
    
    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->isAgent()) {
            return (int) $ticket->assigned_to === (int) $user->id;
        }

        return false;
    }

    /**
     * Assign ticket: admin only (admin handled by before()).
     */

    public function assign(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Comment/reply:
     * - customer: only own ticket
     * - agent: only assigned
     */
    public function comment(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }
}

