@extends('Common.template')

@section('title', ' Plans')
@section('page.title', 'Plans')
@section('page.subtitle', 'View BillingServ Plans')

@section('breadcrumbs')
	<li class="active">View BillingServ Plans</li>
@stop

@section ('content')
	@if (count($errors) > 0)
		<div class="alert alert-danger">
			@foreach ($errors->all() as $error)
				{{$error}}<br>
			@endforeach
		</div>
	@endif

	<div class="box box-primary">
		<div class="box-header with-border">
			<h3 class="box-title">BillingServ Plans</h3>
		</div>

		<div class="box-body">
			<table class="table table-hover table-striped">
				<thead>
					<tr>
						<td class="col-lg-12"></td>
						<td class="col-xs-1"></td>
						<td class="col-xs-1"></td>
						<td class="col-xs-1"></td>
					</tr>
				</thead>
				<tbody>
					@forelse ($plans as $plan)
						<tr>
							<td>{{$plan->name}}</td>
							<td><a href="/plans/{{$plan->id}}"><button class="btn btn-default"><i class="fa fa-edit"></i></button></a></td>
							<td><a href="/plans/delete/{{$plan->id}}"><button class="btn btn-default"><i class="fa fa-trash-o"></i></button></a></td>
						</tr>
					@empty
						<tr>
							<td colspan="4">
								There are currently no plans for BillingServ.
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div class="box-footer no-border">
			<a href="/plans/new"><i class="fa fa-plus"></i> Add Plan</a>
		</div>
	</div>


	<div class="modal fade" id="confirm">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Are you sure?</h4>
					<button type="button" class="close" data-dismiss="modal">×</button>
				</div>
				<div class="modal-body">
					<div class="text-center">
						Are you sure you wish to delete <span id="deleteText"></span>?
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default float-left btn-lrg" data-dismiss="modal">No</button>
					<form method="post" action="#" id="confirmForm">
						<input type="hidden" name="_token" value="{{csrf_token()}}">
						<button type="submit" class="btn btn-success btn-lrg">Yes</button>
					</form>
				</div>
			</div>
		</div>
	</div>
@stop

@section('javascript')
	<script>
		$('a:has(i.fa-trash-o)').click(function(e) {
			$('#confirm').find('#deleteText').text($(this).parent().siblings('.box-title,td:nth-child(1)').text());
			$('#confirm').find('#confirmForm').attr('action', $(this).attr('href'));
			$('#confirm').modal('show');

			return false;
		});
	</script>
@stop
