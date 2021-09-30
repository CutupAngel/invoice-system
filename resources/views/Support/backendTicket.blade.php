@extends ('Common.template')

@section('title', ' Ticket #101')

@section('page.title', 'Support Desk')
@section('page.subtitle', "Ticket Number #{$ticket->id}")

@section('breadcrumbs')
    <li class="active">Ticket #{{ $ticket->id }}</li>
@stop

@section('content')
    <div class="card-body">
        @if (count($errors) > 0)
            <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>
                @foreach ($errors->all() as $error)
                    {{$error}}<br>
                @endforeach
            </div>
        @endif
    </div>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif
    @if(!$user->isCustomer())
    <form class="card" action="{{ route('tickets.update', $ticket->id) }}" method="POST" autocomplete="off">
        @csrf
        @method('PUT')
        <div class="card-header">
            <h3 class="card-title">Update Ticket:</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label>Customer</label>
                        <select name="user_id" class="form-control">
                            @foreach($user->customers()->get() as $customer)
                                <option value="{{ $customer->id }}" {{ $customer->id == $ticket->user_id ? "selected" : "" }}>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control" value="{{ $ticket->subject }}" required>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group mr-2">
                        <label>Assignee</label>
                        <select name="assignee_by" class="form-control">
                            <option value="">--Select--</option>
                            @foreach($user->staff()->get() as $staff)
                                <option value="{{ $staff->id }}" {{ $staff->id == $ticket->assignee_by ? "selected" : "" }}>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="open" {{ $ticket->status === 'open' ? "selected" : "" }}>Open</option>
                            <option value="pending" {{ $ticket->status === 'pending' ? "selected" : "" }}>Pending</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? "selected" : "" }}>Closed</option>
                            <option value="awaiting_replay" {{ $ticket->status === 'awaiting_replay' ? "selected" : "" }}>Awaiting Reply</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" class="form-control">
                            <option value="">--Select--</option>
                            <option value="low" {{ $ticket->priority === 'low' ? "selected" : "" }}>Low</option>
                            <option value="medium" {{ $ticket->priority === 'medium' ? "selected" : "" }}>Medium</option>
                            <option value="high" {{ $ticket->priority === 'high' ? "selected" : "" }}>High</option>
                            <option value="emergency" {{ $ticket->priority === 'emergency' ? "selected" : "" }}>Emergency</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success float-right">Update</button>
        </div>
    </form>
    @endif
    <div class="row">
        <div class="col-md-12">
            @if(!empty($messages))
                <div class="timeline">
                    @foreach($messages as $key => $values)
                        <div class="time-label">
                            <span class="bg-green">{{ $key }}</span>
                        </div>
                        @foreach($values as $message)
                            <div>
                                <i class="fas {{ $message->user->isCustomer() ? 'fa-envelope bg-blue' : 'fa-comments bg-yellow' }}"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $message->createdAtHuman }}</span>
                                    <h3 class="timeline-header"><a href="#">{{ $message->replayByHuman }}</a></h3>

                                    <div class="timeline-body">
                                        {!! $message->message !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    @endforeach
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Update Ticket:</h3>
        </div>
        <div class="card-body">
            <div id="alert" class="alert alert-danger d-none"></div>
            <div class="form-group">
                <textarea name="message" id="summernote" class="form-control" required></textarea>
            </div>
        </div>
        <div class="card-footer">
	           <span class="float-right">
                   <button type="button" id="btn_replay" data-url="{{ route('tickets.reply', ['id' => $ticket->id]) }}" class="btn btn-success">Add Reply</button>
               </span>
        </div>
    </div>
@stop
@section ('javascript')
    <script>
        (function () {
            var summernote = $('#summernote');
            var btnReplay = document.getElementById('btn_replay');
            var alert = document.getElementById('alert');

            summernote.summernote();
            btnReplay.addEventListener('click', function (ev) {
                ev.preventDefault();

                var self = ev.target;
                var message = summernote.summernote('isEmpty')
                    ? ''
                    : summernote.summernote('code');

                self.setAttribute('disabled', true);
                summernote.summernote('disable');

                $.post(self.dataset.url, {message: message})
                    .done(function () {
                        location.reload();
                    })
                    .fail(function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 422) {
                            alert.innerHTML = jqXHR.responseJSON.errors.message[0];
                            alert.classList.remove('d-none');
                        }
                    })
                    .always(function () {
                        self.removeAttribute('disabled');
                        summernote.summernote('enable');
                        summernote.summernote('reset');
                    });
            });
        })();
    </script>
@stop
