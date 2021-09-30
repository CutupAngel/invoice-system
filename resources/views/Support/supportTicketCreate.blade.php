@extends ('Common.template')

@section('title', 'Create Ticket')

@section('page.title', 'Support Desk')
@section('page.subtitle', 'Create Ticket')

@section('breadcrumbs')
    <li class="active">Create Ticket</li>
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
    <form id="create_ticket" class="card" action="{{ route('tickets.store') }}" method="POST" autocomplete="off">
        @csrf
        <div class="card-header">
            <h3 class="card-title">Create Ticket:</h3>
        </div>
        <div class="card-body">
            @if($user->isCustomer())
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Priority</label>
                            <select name="priority" class="form-control">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" class="form-control" required></textarea>
                </div>
            @else
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Select Customer</label>
                            <select name="user_id" class="form-control">
                                @foreach($user->customers()->get() as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group mr-2">
                            <label>Assignee</label>
                            <select name="assignee_by" class="form-control">
                                @foreach($user->staff()->get() as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
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
                                <option value="open">Open</option>
                                <option value="pending">Pending</option>
                                <option value="closed">Closed</option>
                                <option value="awaiting_replay">Awaiting Reply</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Priority</label>
                            <select name="priority" class="form-control">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Message</label>
                            <textarea name="message" class="form-control" required></textarea>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success float-right">Save</button>
        </div>
    </form>
@stop
