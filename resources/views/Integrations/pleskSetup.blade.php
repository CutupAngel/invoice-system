@extends ('Common::template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Integrations')

@section('breadcrumbs')
	<li>Settings</li>
	<li><a href="/settings/integrations">Integrations</a></li>
	<li class="active">Plesk</li>
@stop

@section('content')
	<div class="row">
		<div class="col-sm-6">
			<div class="box">
				<form method="post">
					<input type="hidden" name="_token" value="{{csrf_token()}}">
					<div class="box-header">
						<h3 class="box-title">Plesk Information</h3>
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
							<label for="hostname">Server Hostname:</label>
							<input type="text" name="hostname" id="hostname" class="form-control" value="{{old('hostname', Settings::get('plesk.hostname'))}}" required>
						</div>
						<div class="form-group">
							<label for="username">Username:</label>
							<input type="text" name="username" id="username" class="form-control" value="{{old('username', Settings::get('plesk.username'))}}" required>
						</div>
						<div class="form-group">
							<label for="password">Password:</label>
							<input type="password" name="password" id="password" class="form-control" value="{{old('password', Settings::get('plesk.password'))}}" required>
						</div>
						<div class="form-group">
							<label for="shared">Shared IP Address:</label>
							<input type="text" name="shared" id="shared" class="form-control" value="{{old('shared', Settings::get('plesk.shared'))}}" required>
						</div>
					</div>
					<div class="box-footer">
						<a href="/settings/integrations"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
						<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"></i> Update Settings</button>
					</div>
				</form>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">Plesk Import</h3><br><br>

					<a href="/settings/hc/plesk/import"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Import Customers</button></a>
				</div>

				</div>
			</div>
		</div>
	</div>
@stop