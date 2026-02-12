<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketCommentResource;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Http\Request;

class TicketCommentController extends Controller
{
    public function index(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $comments = $ticket->comments()
            ->with(['author:id,name,email,role'])
            ->latest()
            ->paginate(20);
    
        return TicketCommentResource::collection($comments);
    }

    public function store(Request $request, Ticket $ticket)
    {
        $this->authorize('comment', $ticket);

        $data = $request->validate([
            'body' => ['required', 'string'],
            'is_internal' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();
        $isInternal = (bool) ($data['is_internal'] ?? false);

    
        if ($isInternal && $user->isCustomer()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => $data['body'],
            'is_internal' => $isInternal,
        ]);

        $comment->load(['author:id,name,email,role']);

        return response()->json([
            'message' => 'Comment added.',
            'data' =>new TicketCommentResource($comment),
        ], 201);
    }
}
