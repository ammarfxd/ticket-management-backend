<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    // Status constants
    public const STATUS_OPEN     = 'open';
    public const STATUS_PENDING  = 'pending';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED   = 'closed';

    // Priority constants
    public const PRIORITY_LOW    = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH   = 'high';
    public const PRIORITY_URGENT = 'urgent';


    protected $fillable = [
        'user_id',
        'category_id',
        'assigned_to',
        'subject',
        'description',
        'status',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'category_id' => 'integer',
            'assigned_to' => 'integer',
        ];
    }

    // --- Relationships ---
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }
    
}
