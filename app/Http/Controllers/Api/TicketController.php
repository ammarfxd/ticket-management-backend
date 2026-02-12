<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $user = $request->user();

        $query = Ticket::query()->with([
            'customer:id,name,email,role',
            'assignee:id,name,email,role',
            'category:id,name,slug',
        ]);

        if ($user->isCustomer()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isAgent()) {
            $query->where('assigned_to', $user->id);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->query('priority')){
            $query->where('priority',$priority);
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }


        $tickets = $query->latest()->paginate(10);

        return TicketResource::collection($tickets);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Ticket::class);

        $data = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
        ]);

        $ticket = Ticket::create([
            'user_id' => $request->user()->id,
            'category_id' => $data['category_id'] ?? null,
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'] ?? Ticket::PRIORITY_MEDIUM,
            'status' => Ticket::STATUS_OPEN,
            'assigned_to' => null,
        ]);

       
        $ticket->load(['customer:id,name,email,role','assignee:id,name,email,role','category:id,name,slug']);

        return response()->json([
            'message' => 'Ticket created.',
            'data' => new TicketResource($ticket),
        ], 201);
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        return $ticket->load([
            'customer:id,name,email,role',
            'assignee:id,name,email,role',
            'category:id,name,slug',
            'comments.author:id,name,email,role', 
        ]);

        return new TicketResource($ticket);
    
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $data = $request->validate([
            'status' => ['sometimes', 'in:open,pending,resolved,closed'],
            'priority' => ['sometimes', 'in:low,medium,high,urgent'],
        ]);

        $ticket->fill($data)->save();

        $ticket->refresh()->load(['category:id,name,slug','assignee:id,name,email,role']);

        return response()->json([
            'message' => 'Ticket updated.',
            'data' => new TicketResource($ticket),
        ]);
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $this->authorize('assign', $ticket);

        $data = $request->validate([
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if (! empty($data['assigned_to'])) {
            $assignee = User::findOrFail($data['assigned_to']);

            if (! $assignee->isAgent()) {
                return response()->json([
                    'message' => 'assigned_to must be an agent user.',
                ], 422);
            }
        }

        $ticket->assigned_to = $data['assigned_to'] ?? null;
        $ticket->save();

        return response()->json([
            'message' => 'Ticket assigned.',
            'data' => $ticket->load(['assignee:id,name,email,role']),
        ]);
    }
}
