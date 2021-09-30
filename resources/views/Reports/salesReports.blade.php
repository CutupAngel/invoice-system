@extends('Common.template')

@section('title', ' Reports')

@section('page.title')
	{{ $report }} Sales Report
@stop

@section('page.subtitle')
	{{ $type }} - {{ $date }}
@stop

@section('breadcrumbs')
	<li class="active">{{ $report }} Sales Report</li>
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
	<button type="button" class="btn btn-primary float-right" id="change">Change Report</button>
@stop

@section('javascript')
	<script id="form-change-template" type="text/x-handlebards">
		<div class="modal fade">
			<form method="POST" id="change-form">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title">Change Report</h4>
							<button type="button" class="close" data-dismiss="modal">×</button>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label for="type">Timeframe</label>
								<select name="type" id="type" class="form-control">
									<option>Yearly</option>
									<option>Monthy</option>
									<option>Daily</option>
								</select>
							</div>

							<div class="form-group">
								<label for="date">Date: </label>
								<input type="date" class="form-control" name="date" id="date" value="{{ $actualDate }}">
							</div>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-success">Run Report</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</script>

	<script>
		(function($) {
			var report = window.location.pathname.split('/')[2];
			$('#change').on('click', function() {
				var $template = $(Handlebars.compile($('#form-change-template').html())());
				$template.modal('show');
			});

			$('body').on('submit', '#change-form', function() {
				var type = $(this).find('#type').val();
				var date = $(this).find('#date').val();

				window.location.pathname = '/reports/' + report + '/' + type + '/' + date;
				return false;
			});

			$('#history').DataTable({
				'paging': true,
				'searching': true,
				'ordering': true
			});
		})($);
	</script>
@stop
