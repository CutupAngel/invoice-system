@extends('Common.template')

@section('title', ' Reports')

@section('page.title', 'Login History')

@section('breadcrumbs')
	<li class="active">Login History</li>
@stop

@section('content')
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">Previous Logins for {{Auth::User()->username}}</h3>
		</div>
		<div class="card-body table-responsive">
			<table id="history" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>Login Time</th>
						<th>Logout Time</th>
						<th>IP Address</th>
						<th>Failed Login</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($history as $record)
						<tr>
							<td>{{$record->created_at}}</td>
							<td>{{$record->logout}}</td>
							<td>{{$record->ip}}</td>
							<td>{{$record->failed ? 'Yes' : 'No'}}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
@stop

@section('javascript')
	<script>
		$('#history').DataTable({
			'paging': true,
			'searching': true,
			'ordering': true,
			'order': [[0, 'desc']]
		});
	</script>
@stop
