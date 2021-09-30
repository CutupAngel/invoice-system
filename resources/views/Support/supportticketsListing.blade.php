@extends ('Common.template')

@section('title', ' Support')

@section('page.title', 'Support')
@section('page.subtitle', 'Support Tickets')

@section('breadcrumbs')
	<li>Support</li>
	<li class="active">Support Tickets</li>
@stop

@section('content')
<div class="card">
	<div class="card-header">
        <h3 class="card-title">Support Tickets</h3>
    </div>
		<div class="card-body p-0 table-responsive">
			@if (session('status'))
				<div class="alert alert-dismissible alert-success">
					{{ session('status') }}
				</div>
			@endif
			<table id="supportList" class="table table-striped projects">
				<thead>
					<tr>
						<th>Client</th>
						<th>Subject</th>
						<th>Assignee</th>
						<th>Status</th>
						<th>Priority</th>
            			<th>Last Action</th>
            			<th>Created At</th>
            			<th>Actions</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		<div class="card-footer">
			<a href="{{ route('tickets.create') }}" class="float-right btn btn-success">Compose</a>
		</div>
	</div>
@stop

@section('javascript')
	<script type="text/javascript">
		(function () {
		    var table = $('#supportList');
		    table.DataTable({
                serverSide: true,
                processing: true,
                ajax: '{{ route('tickets.datatables') }}',
                columns: [
					{data: 'user_id', searchable: false, orderable: false },
					{data: 'subject', name: 'subject'},
					{data: 'assignee_by', searchable: false, orderable: false},
					{data: 'status', name: 'status' },
					{data: 'priority', name: 'priority' },
					{data: 'last_action', name: 'last_action', searchable: false, orderable: false },
					{data: 'created_at', name: 'created_at', searchable: false },
					{data: 'actions', searchable: false, orderable: false, sClass: 'text-center'}
				],
            	order: [[6, 'desc']]
			});

            table.on('draw.dt', function () {
                [...document.querySelectorAll('.edit-item')].forEach(function (el) {
                    el.addEventListener('click', function (event) {
                        event.preventDefault();
                        const dataset = event.currentTarget.dataset;
                        location.href = dataset.url
                    });
                });
            });
        })()
	</script>
@endsection
