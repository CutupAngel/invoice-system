@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Staff Logins')

@section('breadcrumbs')
	<li class="active">{{ trans('backend.staff-welcome') }}</li>
@stop

@section('content')
	<div class="card">
		<div class="card-body table-responsive">
			@if(session('status'))
				<div class="alert alert-dismissible alert-success">
					<button type="button" class="close" data-dismiss="alert">×</button>
					{{ session('status') }}
				</div>
			@elseif(session('error'))
				<div class="alert alert-dismissible alert-danger">
					<button type="button" class="close" data-dismiss="alert">×</button>
					{{ session('error') }}
				</div>
			@endif
			@if (count($errors) > 0)
				<div class="alert alert-dismissible alert-danger">
					<button type="button" class="close" data-dismiss="alert">×</button>
					@foreach ($errors->all() as $error)
						{{$error}}<br>
					@endforeach
				</div>
			@endif
			<table id="staffList" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>{{ trans('backend.staff-id') }}</th>
						<th>{{ trans('backend.staff-name') }}</th>
						<th>{{ trans('backend.staff-username') }}</th>
						<th>{{ trans('backend.staff-email') }}</th>
						<th>{{ trans('backend.staff-status') }}</th>
						<th class="no-sort tools"></th>
					</tr>
				</thead>
				<tbody>
					@foreach($staff as $user)
						<tr data-id="{{$user->id}}">
							<td>{{$user->id}}</td>
							<td>{{$user->name}}</td>
							<td>{{$user->username}}</td>
							<td>{{$user->email}}</td>
							<td>{{ ($user->trashed() ? 'Inactive' : 'Active') }}</td>
							<td class="tools">
								<a href="/settings/staff/{{$user->id}}/edit" class="btn btn-sm btn-warning">Edit</a>
								<a href="#" class="btn btn-sm delete btn-danger">Delete</a>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
	<a href="/settings/staff/create" class="float-right"><button class="btn btn-success"><i class="fa fa-plus"></i> {{ trans('backend.staff-add') }}</button></a>
@stop

@section('javascript')
	<script>
		var staffList = $('#staffList').DataTable({
			'paging': true,
			'searching': true,
			'ordering': true,
			'columnDefs': [ {
				"targets": "no-sort",
				"orderable": false
			}],
			"language": {
				'emptyTable': 'There are currently 0 staff members.'
			}
		});

		$('.delete').on('click', function() {
			var id = $(this).parents('tr').data('id');

			if (confirm("Are you sure you want to delete this user?")) {
				$.ajax({
					url: '/settings/staff/' + id,
					type: 'DELETE'
				})
				.done(function() {
					staffList.row($('[data-id="'+id+'"]'))
						.remove()
						.draw();
				})
				.fail(function() {
					alert('An error occurred while deleting the user.');
				});
			}
		});
	</script>
@stop
