@extends ('Common::template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Integrations')

@section('breadcrumbs')
	<li>Settings</li>
	<li><a href="/settings/integrations">Integrations</a></li>
	<li class="active">OpenSRS</li>
@stop

@section('content')
	<div class="row">
		<div class="col-sm-6">
			<form method="POST">
				<input type="hidden" name="_token" value="{{csrf_token()}}">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">OpenSRS Information</h3>
					</div>
					<div class="box-body">
						@if (count($errors) > 0)
							<div class="alert alert-danger">
								<ul>
									@foreach ($errors->all() as $error)
										<li>{{$error}}</li>
									@endforeach
								</ul>
							</div>
						@endif

						@if (session('status'))
							<div class="alert alert-success">
								{{session('status')}}
							</div>
						@endif
						<div class="form-group">
							<label for="username">Username: </label>
							<input type="text" name="username" id="username" class="form-control" value="{{ old('username', Settings::get('opensrs.username')) }}" required>
						</div>
						<div class="form-group">
							<label for="apikey">API Key: </label>
							<span class="checkbox test">
								<label>
									<input type="checkbox" name="testmode"  {{ old('testmode', Settings::get('opensrs.testmode')) == null ?: 'checked' }}>
									Enable Test Mode
								</label>
							</span>
							<input type="text" name="apikey" id="apikey" class="form-control" value="{{ old('apikey', Settings::get('opensrs.apikey')) }}" required>
						</div>
						<div class="form-group">
							<label for="ns1">Default NS 1: </label>
							<input type="text" name="ns1" id="ns1" class="form-control" value="{{ old('ns1', Settings::get('opensrs.ns1')) }}" required>
						</div>
						<div class="form-group">
							<label for="ns2">Default NS 2: </label>
							<input type="text" name="ns2" id="ns2" class="form-control" value="{{ old('ns2', Settings::get('opensrs.ns2')) }}" required>
						</div>
					</div>
					<div class="box-footer">
						<a href="/settings/integrations"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
						<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"> Update Settings</i></button>
					</div>
				</div>
			</form>
		</div>
		<div class="col-sm-6">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">OpenSRS Import</h3>
				</div>
				<div class="box-body">
					<a href="/settings/domains/enom/import"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Import Customers</button></a>
				</div>
			</div>
		</div>
	</div>
@stop

@section ('css')
	<style>
		.checkbox.test {
			display: inline-block;
			margin-top: 0;
			margin-bottom: 0;
			margin-left: 10px;
		}
	</style>
@stop