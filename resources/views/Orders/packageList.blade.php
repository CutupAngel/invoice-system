@extends('Common.template')

@section('title', ' Packages')
@section('page.title', 'Packages')
@section('page.subtitle', 'View Packages')

@section('breadcrumbs')
	<li class="active">View Packages</li>
@stop

@section ('content')

	@if (session('status'))
			<div class="alert alert-success">
					{{ session('status') }}
			</div>
	@endif

	@if (count($errors) > 0)
		<div class="alert alert-danger">
			@foreach ($errors->all() as $error)
				{{$error}}<br>
			@endforeach
		</div>
	@endif

	@forelse ($groups as $group)
	<div class="card">
			<div class="card-header">
				<h3 class="card-title">
					{{$group->name}}
				</h3>
				<div class="float-right card-tools">
					<a href="/orders/toggle/{{$group->id}}" class="toggle">
						<button class="btn btn-primary ">
							@if ($group->visible === '1')
								<i class="fa fa-eye"></i>
							@else
								<i class="fa fa-eye-slash"></i>
							@endif
						</button>
					</a>
					<a href="/order/{{str_slug(urldecode($group->url))}}" target="_blank">
						<button class="btn btn-primary">
							<i class="fa fa-search"></i>
						</button>
					</a>
					<a href="/orders/{{$group->id}}">
						<button class="btn btn-primary">
							<i class="fa fa-edit"></i>
						</button>
					</a>
					<a href="/orders/delete/{{$group->id}}">
						<button class="btn btn-primary">
							<i class="fas fa-trash"></i>
						</button>
					</a>
				</div>
			</div>

			<div class="card-body p-0">
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
						@forelse ($group->packages as $package)
							<tr>
								<td>{{$package->name}}</td>
								<td><a href="/order/{{ $group->id }}/{{ $package->id }}" target="_blank"><button class="btn btn-default"><i class="fa fa-search"></i></button></a></td>
								<td><a href="/orders/{{$group->id}}/{{$package->id}}"><button class="btn btn-default"><i class="fa fa-edit"></i></button></a></td>
								<td><a href="/orders/delete/{{$group->id}}/{{$package->id}}"><button class="btn btn-default"><i class="fa fa-trash"></i></button></a></td>
							</tr>
						@empty
							<tr>
								<td colspan="4">
									There are currently no packages in this group.
								</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			<div class="card-footer no-border">
				<a href="/orders/{{$group->id}}/new"><button class="btn btn-primary"><i class="fa fa-plus"></i> Add Package</button></a>
			</div>
		</div>
	@empty
		<div class="alert alert-info">
			There are currently no groups setup.
		</div>
	@endforelse

<div class="row">
	<div class="col-12">
		<a href="/orders/new"><button class="btn btn-primary float-right mb-3"><i class="fa fa-plus"></i> Add Group</button></a>
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
		$('a:has(i.fa-trash)').click(function(e) {
			$('#confirm').find('#deleteText').text($(this).parent().siblings('.box-title,td:nth-child(1)').text());
			$('#confirm').find('#confirmForm').attr('action', $(this).attr('href'));
			$('#confirm').modal('show');

			return false;
		});

		$('a.toggle').click(function() {
			$self = $(this);
			$.ajax({
				url: $self.attr('href'),
				type: 'PUT',
				dataType: 'JSON'
			})
			.done(function(d) {
				if (d === 0) {
					$self.find('i').removeClass('fa-eye').addClass('fa-eye-slash');
				} else {
					$self.find('i').removeClass('fa-eye-slash').addClass('fa-eye');
				}
			});

			return false;
		})
	</script>
@stop
