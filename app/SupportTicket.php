<?php

namespace App;

use Yajra\DataTables\DataTables;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany(SupportTicketMessage::class);
    }

    /**
     * @return mixed
     */
    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at->diffForHumans();
    }

    /**
     * @return mixed
     */
    public function getTimeLineMessages()
    {
        return $this->messages
            ->mapToGroups(function ($message) {
                $createDate = $message->created_at->format('d M Y');
                return [$createDate => $message];
            })
            ->all();
    }

    /**
     * @return mixed
     */
    public function getDatatables()
    {
        $user = auth()->user();
        $query = $user->isCustomer()
            ? $this->where('user_id', $user->id)
            : $this->query();

        return DataTables::of($query)
            ->editColumn('user_id', function ($ticket) {
                return $ticket->user ? $ticket->user->name : null;
            })
            ->editColumn('assignee_by', function ($ticket) {
                return $ticket->assignee ? $ticket->assignee->name : null;
            })
            ->editColumn('created_at', function ($ticket) {
                return $ticket->created_at->toDatetimeString();
            })
            ->addColumn('actions', function ($ticket) {
                return '<a class="btn btn-success edit-item" data-url="'.route('tickets.edit', ['id' => $ticket->id]).'" href="javascript:void(0)" title="Update">Update</a>';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

}
