@extends ('Common::template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Integrations')

@section('breadcrumbs')
	<li>Settings</li>
	<li><a href="/settings/integrations">Integrations</a></li>
	<li class="active">Campaign Monitor</li>
@stop

@section('content')
	<div class="row">
		<div class="col-sm-6">
			<form method="POST">
				<input type="hidden" name="_token" value="{{csrf_token()}}">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">Campaign Monitor</h3>
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
							<label for="client">Client ID:</label>
							<input type="text" id="client" name="client" class="form-control" value="{{old('client', Settings::get('campaignmonitor.clientid'))}}">
						</div>
						<div class="form-group">
							<label for="api">API Key:</label>
							<input type="text" id="api" name="api" class="form-control" value="{{old('api', Settings::get('campaignmonitor.apikey'))}}">
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
					<h3 class="box-title">Campaign Monitor Import</h3><br><br>
					<a href="/settings/mail/campaignmonitor/import"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Import Customers</button></a>
				</div>
			</div>
		</div>
	</div>
@stop