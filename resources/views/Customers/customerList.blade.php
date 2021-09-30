@extends ('Common.template')

@section('title', 'Customers')

@section('page.title', 'Customers')
@section('page.subtitle', 'List')

@section('breadcrumbs')
	<li class="active">{{ trans('backend.cust-welcome') }}</li>
@stop

@section('content')

@if (session('status'))
		<div class="alert alert-success">
				{{ session('status') }}
		</div>
@endif

	<div class="card">
		<div class="card-body">
			<div class="row">
			<div class="col-sm-12 table-responsive">
			<table id="customerList" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>{{ trans('backend.cust-ID') }}</th>
						<th>{{ trans('backend.cust-customer') }}</th>
						<th>{{ trans('backend.cust-email') }}</th>
						<th>{{ trans('backend.cust-location') }}</th>
						<th>{{ trans('backend.cust-telephone') }}</th>
						<th>{{ trans('backend.cust-status') }}</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@foreach($customers as $customer)
						<tr data-id="{{ $customer->id }}">
							<td><a href="/customers/{{$customer->id}}">{{$customer->id}}</a></td>
							<td>{{$customer->name}}</td>
							<td>{{ $customer->hasAddress('mailing') ? $customer->mailingContact->address->email : ''}}</td>
							<td>{{ $customer->hasAddress('mailing') ? @$customer->mailingContact->address->country->name : ''}}</td>
							<td>{{ $customer->hasAddress('mailing') ? $customer->mailingContact->address->phone : ''}}</td>
							<td>
								@if ($customer->trashed())
									Deactivated
								@else
									Active
								@endif
							</td>
							<td class="card-tools">
								<a href="/customers/{{$customer->id}}/edit"><i class="fas fa-pencil-alt"></i></a>
								@if ($customer->trashed())
									<a href="#" class="restore"><i class="fas fa-sync-alt"></i></a>
								@else
									<a href="#" class="delete"><i class="fas fa-trash"></i></a>
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>
</div>
@stop

@section('javascript')

	<script>
		(function($) {
			var customerTable = $('#customerList').DataTable({
				'paging': true,
				'searching': true,
				'ordering': true,
				'order': [ [5, 'asc'], [1, 'asc' ]],
				"language": {
					'emptyTable': 'There are currently 0 customers.'
				}
			});

			window.customerTable = customerTable;

			$('body').on('click', '.delete', function() {
				var id = $(this).parents('tr').data('id');

				if (confirm("{{ trans('backend.cust-deletewarning') }}")) {
					/* $.ajax({
						url: '/customers/' + id,
						type: 'DELETE'
					})
					.done(function() {
						ar data = customerTable.row($('[data-id="'+id+'"]')).data();
						data[5] = 'Deactivated';
						data[6] = data[6].replace('delete', 'restore').replace('fa-trash-o', 'fa-refresh');
						customerTable.row($('[data-id="'+id+'"]')).data(data);
					})
					.fail(function() {
						alert('An error occurred while deleting the customer.');
					}); */

					window.location.href = '/customers/delete/' + id;
				}

				return false;
			});

			$('body').on('click', '.restore', function() {
				var id = $(this).parents('tr').data('id');

				if (confirm("Are you sure you want to restore this customer?")) {
					$.ajax({
						url: '/customers/' + id + '/restore',
						type: 'PUT'
					})
					.done(function() {
						var data = customerTable.row($('[data-id="'+id+'"]')).data();
						data[5] = 'Active';
						data[6] = data[6].replace('restore', 'delete').replace('fa-refresh', 'fa-trash-o');
						customerTable.row($('[data-id="'+id+'"]')).data(data);
					})
					.fail(function() {
						alert('An error occurred while restoring the customer.');
					});
				}

				return false;
			});
		})(jQuery);
	</script>
@stop
