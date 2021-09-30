@extends ('Common::template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Integrations')

@section('breadcrumbs')
	<li>Settings</li>
	<li><a href="/settings/integrations">Integrations</a></li>
	<li class="active">Proxmox VE</li>
@stop

@section('content')
	<div class="row">
		<div class="col-sm-6">
			<form method="POST">
				<input type="hidden" name="_token" value="{{csrf_token()}}">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">Proxmox VE</h3>
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
							<label for="hostname">Hostname: </label>
							<input type="text" name="hostname" id="hostname" class="form-control" value="{{old('hostname', Settings::get('proxmox.hostname'))}}" required>
						</div>
						<div class="form-group">
							<label for="port">Port: </label>
							<input type="number" name="port" id="port" class="form-control" value="{{old('port', Settings::get('proxmox.port'))}}" placeholder="9009">
						</div>
						<div class="form-group">
							<label for="username">Username: </label>
							<input type="text" name="username" id="username" class="form-control" value="{{old('username', Settings::get('proxmox.username'))}}" required>
						</div>
						<div class="form-group">
							<label for="password">Password: </label>
							<input type="password" name="password" id="password" class="form-control" value="{{old('password', Settings::get('proxmox.password'))}}" required>
						</div>
						<div class="form-group">
							<label for="realm">Realm: </label>
							<input type="text" name="realm" id="realm" class="form-control" value="{{old('realm', Settings::get('proxmox.realm'))}}" placeholder="pve">
						</div>
					</div>
					<div class="box-footer">
						<a href="/settings/integrations"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
						<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"></i> Update Settings</button>
					</div>
				</div>
			</form>
		</div>
		<div class="col-sm-6">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">cPanel/WHM Import</h3><br><br>
					<a href="/settings/hc/cpanel/import"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Import Customers</button></a>
				</div>
			</div>
		</div>
	</div>
@stop

@section ('css')
	<style>
		.checkbox.ssl {
			display: inline-block;
			margin-top: 0;
			margin-bottom: 0;
			margin-left: 10px;
		}
	</style>
@stop