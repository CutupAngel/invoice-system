<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupportTicketMessage extends Model
{
    protected $guarded = [];

    /**
     * @return mixed
     */
    public function getCreatedAtHumanAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * @return string
     */
    public function getReplayByHumanAttribute()
    {
        $user = $this->user;
        $label = $user->isCustomer() ? 'customer' : ($user->isStaff() ? 'staff' : 'admin');
        return "{$user->name} ({$label})";
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'replay_by');
    }
}
