@extends ('Common.template')

@section('title', ' Invoices :: List')

@section('page.title', 'View Invoices')
@section('page.subtitle', ucfirst($type))

@section('breadcrumbs')
	<li class="active">View Invoices</li>
@stop

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
			<div class="col-sm-12 table-responsive">
			<table id="invoiceList" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>Num.</th>
						<th>To</th>
						<th>Trial</th>
						<th>Amount</th>
						<th>Status</th>
						<th>Issued</th>
						<th>Due</th>
						<th class="no-sort tools"></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
			</div>
		</div>
	</div>
</div>
@stop

@section('javascript')

	<script>
		var invoiceTable = $('#invoiceList').DataTable({
			'paging': true,
			'searching': true,
			'ordering': true,
			'columnDefs': [ {
				"targets": "no-sort",
				"orderable": true
			}],
			"language": {
				'emptyTable': 'There are currently 0 invoices.'
			},
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "/admin/invoices/list/",
				"method": "POST",
				"data": function(d){
					return $.extend({}, d, {
						"_token": '{{csrf_token()}}',
						"invoice_type": "{{$type}}"
					});
				}
			}
		});

		$('body').on('click','.delete', function() {
			var id = $(this).data('invoice');

			if (confirm("Are you sure you want to delete this invoice?")) {
				$.ajax({
					url: '/admin/invoices/' + id,
					type: 'DELETE'
				})
				.done(function() {
					invoiceTable.row($('[data-id="'+id+'"]'))
						.remove()
						.draw();
				})
				.fail(function() {
					alert('An error occurred while deleting the invoice.');
				});
			}
		});
	</script>
@stop
