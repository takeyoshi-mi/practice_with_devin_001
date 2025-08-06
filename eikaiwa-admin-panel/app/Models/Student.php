<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class Student extends Model
{
    protected $fillable = [
        'student_id',
        'nickname', 
        'name',
        'email',
        'remaining_tickets',
    ];

    protected $casts = [
        'remaining_tickets' => 'integer',
    ];

    public function plans(): HasMany
    {
        return $this->hasMany(StudentPlan::class);
    }

    public function currentPlan(): HasOne
    {
        return $this->hasOne(StudentPlan::class)
            ->where('is_active', true)
            ->latest('start_date');
    }

    public function ticketHistories(): HasMany
    {
        return $this->hasMany(TicketHistory::class)
            ->orderBy('created_at', 'desc');
    }

    public function addTickets(int $count, User $addedBy, ?string $notes = null): void
    {
        DB::transaction(function () use ($count, $addedBy, $notes) {
            $this->increment('remaining_tickets', $count);
            
            $this->ticketHistories()->create([
                'added_by_user_id' => $addedBy->id,
                'ticket_count' => $count,
                'action_type' => 'add',
                'notes' => $notes,
            ]);
        });
    }
}
