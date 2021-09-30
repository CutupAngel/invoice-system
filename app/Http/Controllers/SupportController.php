<?php

namespace App\Http\Controllers;

use App\Notifications\SupportTicketCreated;
use App\SupportTicket;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class SupportController extends Controller
{
    /**
     * @var SupportTicket
     */
    protected $tickets;

    /**
     * SupportController constructor.
     * @param SupportTicket $ticket
     */
	public function __construct(SupportTicket $ticket)
	{
		$this->tickets = $ticket;
	}

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
	public function getTickets()
	{
		return view('Support.supportticketsListing');
	}

    /**
     * @return mixed
     */
	public function getDatatables()
    {
        return $this->tickets->getDatatables();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createTicket(Request $request)
    {
        $user = $request->user();
        return view('Support.supportTicketCreate', compact('user'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeTicket(Request $request)
    {
        $user = $request->user();
        $isCustomer = $user->isCustomer();

        $rules = [
            'subject' => 'required|string',
            'priority' => 'required|in:low,medium,high,emergency',
            'message' => 'required|string'
        ];

        if (!$isCustomer) {
            $rules = array_merge($rules, [
                'user_id' => 'required|integer',
                'assignee_by' => 'required|integer',
                'status' => 'required|in:open,pending,closed,awaiting_replay'
            ]);
        }

        $this->validate($request, $rules);

        if ($isCustomer) {
            $request->request->add(['status' => 'open']);
        }

        $request->merge(['last_action' => 'Created']);

        $ticket = $user->supportTickets()
            ->create($request->except('message'));

        $ticket->messages()->create([
            'message' => $request->message,
            'replay_by' => $user->id,
        ]);

        $this->sendNotificationToClient($user, $ticket, $request->user_id ?? 0);

        return redirect()
            ->route('tickets.index')
            ->with('status', 'New Ticket has been created!');
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editTicket(Request $request, $id)
    {
        $ticket = $this->tickets->findOrFail($id);
        $user = $request->user();
        $messages = $ticket->getTimeLineMessages();

        return view('Support.backendTicket', compact('ticket', 'user', 'messages'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTicket(Request $request, $id)
    {
        $this->validate($request, [
            'user_id' => 'required|integer',
            'status' => 'required|in:open,pending,closed,awaiting_replay',
            'assignee_by' => 'required|integer',
            'priority' => 'required|in:low,medium,high,emergency',
            'subject' => 'required|string',
        ]);

        $ticket = $this->tickets->findOrFail($id);

        $request->merge([
            'last_action' => 'Updated'
        ]);

        $ticket->update($request->all());

        return redirect()
            ->route('tickets.index')
            ->with('status', "Ticket ID: {$id} has been updated");
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function replyTicket(Request $request, $id)
    {
        $this->validate($request, ['message' => 'required|string']);

        $user = $request->user();
        $ticket = $this->tickets->findOrFail($id);

        $ticket->update(['last_action' => 'Replied']);

        $request->merge([
            'replay_by' => $user->id
        ]);

        $ticket->messages()
            ->create($request->all());

        $this->sendNotificationToClient($user, $ticket, $ticket->user_id, true);

        return response()->json(null, 201);
    }

    /**
     * @param $user
     * @param $ticket
     * @param int $userId
     * @param $replayBy
     */
    protected function sendNotificationToClient($user, $ticket, $userId, $replayBy = false)
    {
        if ($user->isCustomer()) {
            $parent = $user
                ->parent()
                ->first();

            $users = $parent->staff()
                ->get()
                ->merge([$parent, $user])
                ->all();
        } else {
            $users = User::findOrFail($userId);
        }

        Notification::send($users, new SupportTicketCreated($ticket, $replayBy ? $user : null));
    }
}
