@extends('Common.template')

@section('title', ' Reports')

@section('page.title')
	{{$reportTitle}}
@stop

@section('breadcrumbs')
	<li class="active">{{$reportTitle}}</li>
@stop

@section('content')
	<div class="card">
		<div class="card-body table-responsive">
			<table id="report" class="table table-bordered table-striped">
				<thead>
					<tr>
						@foreach ($columns as $column)
							<td>{{$column}}</td>
						@endforeach
					</tr>
				</thead>
				<tbody>
					@foreach ($records as $record)
						<tr>
							@foreach ($record as $value)
								<td>{{$value}}</td>
							@endforeach
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
			'ordering': true
		});
	</script>
@stop
