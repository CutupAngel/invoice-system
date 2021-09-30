@extends ('Common.template')
@if($type === 'estimates')
	@section('title', ' Estimates :: List')

	@section('page.title', 'View Estimates')
	@section('page.subtitle', ucfirst($type))

	@section('breadcrumbs')
		<li class="active">View Estimates</li>
	@stop
@else

	@section('title', ' Invoices :: List')

	@section('page.title', 'View Invoices')
	@section('page.subtitle', ucfirst($type))

	@section('breadcrumbs')
		<li class="active">View Invoices</li>
	@stop
@endif

@section('content')
	<div class="card">
		<div class="card-body table-responsive">
			<table id="invoiceList" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th class="sorting_asc col-md-2">Num.</th>
						<th class="sorting col-md-2">Amount</th>
						@if($type !== 'estimates')
						<th class="sorting col-md-2">Status</th>
						@endif
						<th class="sorting col-md-2">Issued</th>
						<th class="sorting col-md-2">Due</th>
						<th class="no-sort tools"></th>
					</tr>
				</thead>
				<tbody>
					@foreach ($invoices as $invoice)
						<tr data-id="{{$invoice->id}}">
							<td><a href="/invoices/{{$invoice->id}}">{{ $invoice->user->getSetting('invoice.prefix', '') }}{{ $invoice->invoice_number }}</a></td>
							<td>{{ number_format($invoice->total, 2) }}</td>
							@if($type !== 'estimates')
							<td>{{ $invoice->status() }}</td>
							@endif
							<td>{{ date('d/m/Y', strtotime($invoice->created_at)) }}</td>
							<td>{{ date('d/m/Y', strtotime($invoice->due_at)) }}</td>
							<td class="card-tools">
								<a href="/invoices/{{ $invoice->id }}/pay"><i class="fa fa-credit-card"></i> Pay</a>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
@stop

@section('javascript')
	<script src="https://v2.b-cdn.uk/plugins/datatables/jquery.dataTables.js"></script>
	<script src="https://v2.b-cdn.uk/plugins/datatables/dataTables.bootstrap.min.js"></script>
@stop

@section('css')
	<link rel="stylesheet" href="https://v2.b-cdn.uk/plugins/datatables/dataTables.bootstrap.css">
	<style>
		td.tools, th.tools {
			width: 30px;
		}
	</style>
@stop
