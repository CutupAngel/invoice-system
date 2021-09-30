@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle')
	@if ($user->id)
		Modify Staff Account
	@else
		Create Staff Account
	@endif
@stop

@section('breadcrumbs')
	<a href="/settings/staff">{{ trans('backend.staff-newwelcome') }}</a>
	<li class="breadcrumb-item active">@if ($user->id)Edit @else Add @endif Staff</li>
@stop

@section('content')
	<form method="post" action="/settings/staff/{{ $user->id }}">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">

		@if ($user->id)
			<input type="hidden" name="_method" value="PUT">
		@endif

		@if (count($errors) > 0)
			<div class="alert alert-danger">
				@foreach ($errors->all() as $error)
					{{$error}}<br>
				@endforeach
			</div>
		@endif

		<div class="row">
			<div class="col-sm-9">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">{{ trans('backend.staff-userinfo') }}</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="name">{{ trans('backend.staff-name') }}: </label>
							<input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
						</div>
						<div class="form-group">
							<label for="email">{{ trans('backend.staff-email') }}: </label>
							<input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
						</div>
						<div class="form-group">
							<label for="username">{{ trans('backend.staff-username') }}: </label>
							<input type="text" name="username" id="username" class="form-control" value="{{ old('username', $user->username) }}" required>
						</div>
						<div class="form-group">
							<label for="password">{{ trans('backend.staff-pass') }}: </label>
							<input
								type="password"
								name="password"
								id="password"
								class="form-control"
								@if ($user->id)
									placeholder="Leave blank for no change"
								@else
									placeholder="Leave blank for autogeneration"
								@endif
							>
						</div>
						<div class="form-group">
							<label for="password_confirmation">{{ trans('backend.staff-passconf') }}: </label>
							<input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-3">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">{{ trans('backend.staff-perms') }}</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="permission[dashboard]" value="Y" checked disabled>
									{{ trans('backend.staff-dash') }}
								</label>
							</div>
						</div>
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="permission[packages]" value="Y" {{ empty(old('permission.packages', $permission['packages'])) ? '' : 'checked' }}>
									{{ trans('backend.staff-pack') }}
								</label>
							</div>
						</div>
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="permission[customers]" value="Y" {{ empty(old('permission.customers', $permission['customers'])) ? '' : 'checked' }}>
									{{ trans('backend.staff-cust') }}
								</label>
							</div>
						</div>
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="permission[invoices]" value="Y" {{ empty(old('permission.invoices', $permission['invoices'])) ? '' : 'checked' }}>
									{{ trans('backend.staff-inv') }}
								</label>
							</div>
						</div>
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="permission[marketing]" value="Y" {{ empty(old('permission.marketing', $permission['marketing'])) ? '' : 'checked' }}>
									{{ trans('backend.staff-mark') }}
								</label>
							</div>
						</div>
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="permission[reports]" value="Y" {{ empty(old('permission.reports', $permission['reports'])) ? '' : 'checked' }}>
									{{ trans('backend.staff-reports') }}
								</label>
							</div>
						</div>
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="permission[support]" value="Y" {{ empty(old('permission.support', $permission['support'])) ? '' : 'checked' }}>
									{{ trans('backend.staff-support') }}
								</label>
							</div>
						</div>
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="permission[settings]" value="Y" {{ empty(old('permission.settings', $permission['settings'])) ? '' : 'checked' }}>
									{{ trans('backend.staff-settings') }}
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<a href="/settings/staff"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> {{ trans('backend.staff-return') }}</button></a>

		@if ($user->id)
			<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"> {{ trans('backend.staff-edit') }}</i></button>
		@else
			<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"> {{ trans('backend.staff-create') }}</i></button>
		@endif
	</form>
@stop
