@extends ('Common.template')

	@section('title', ' Orders :: List')

	@section('page.title', 'Orders')
	@section('page.subtitle', 'View Orders')

	@section('breadcrumbs')
		<li class="active">View Orders</li>
	@stop

@section('content')
	<div class="card">
		<div class="card-body">
			<table id="orderList" class="table table-bordered table-striped">
				@if (sizeof($orders) > 0)
				<thead>
					<tr>
						<th class="sorting col-md-4">Package</th>
						<th class="sorting col-md-4">Status</th>
						<th class="sorting_desc col-md-4">Order Date</th>
						<th class="no-sort tools"></th>
					</tr>
				</thead>
				<tbody>
					@foreach ($orders as $order)
						<tr data-id="{{$order->id}}">
							<td><a href="/products-ordered/order/{{$order->id}}">{{ $order->package ? $order->package->name : '' }}</a></td>
							<td>{{ $order->status }}</td>
							<td>{{ date('d/m/Y', strtotime($order->created_at)) }}</td>
							<td class="card-tools">
								<a href="/products-ordered/order/{{$order->id}}">View</a>
							</td>
						</tr>
					@endforeach
				</tbody>
				@else
					<p>Order list is empty</p>
				@endif
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
