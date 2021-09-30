@extends ('Common::template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Integrations')

@section('breadcrumbs')
	<li>Settings</li>
	<li><a href="/settings/integrations">Integrations</a></li>
	<li class="active">eNom</li>
@stop

@section('content')
	<form method="POST">
		<div class="row">
			<div class="col-sm-6">
				<input type="hidden" name="_token" value="{{csrf_token()}}">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">eNom Information</h3>
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
							<label for="userid">User ID: </label>
							<input type="text" name="userid" id="userid" class="form-control" value="{{old('userid', Settings::get('enom.userid'))}}" required>
						</div>
						<div class="form-group">
							<label for="key">Password: </label>
							<span class="checkbox test">
								<label>
									<input type="checkbox" name="testmode"  {{ old('testmode', Settings::get('enom.testmode')) === null ?: 'checked' }}>
									Enable Test Mode
								</label>
							</span>
							<input type="password" name="password" id="password" class="form-control" value="{{old('password', Settings::get('enom.password'))}}" required>
						</div>
						<div class="form-group">
							<label for="ns1">Default NS 1: </label>
							<input type="text" name="ns1" id="ns1" class="form-control" value="{{old('ns1', Settings::get('enom.ns1'))}}" required>
						</div>
						<div class="form-group">
							<label for="ns2">Default NS 2: </label>
							<input type="text" name="ns2" id="ns2" class="form-control" value="{{old('ns2', Settings::get('enom.ns2'))}}" required>
						</div>
					</div>
					<div class="box-footer">
						<a href="/settings/integrations"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
						<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"></i> Update Settings</button>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">Pricing Table</h3>
					</div>
					<div class="box-body">
						@if (empty($tlds))
							Please validate your credentials before modifying the pricing table.
						@else
							<table class="table">
								<thead>
									<tr>
										<th>TLD</th>
										<th>Register</th>
										<th>Renew</th>
										<th>Transfer</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($tlds as $tld => $info)
										<tr>
											<td>{{$tld}}</td>
											<td><input type="text" name="tld[{{$tld}}][register]" value="{{number_format(old("tld.$tld.register", $info['register']), 2)}}"></td>
											<td><input type="text" name="tld[{{$tld}}][renew]" value="{{number_format(old("tld.$tld.renew", $info['renew']), 2)}}"></td>
											<td><input type="text" name="tld[{{$tld}}][transfer]" value="{{number_format(old("tld.$tld.transfer", $info['transfer']), 2)}}"></td>
										</tr>
									@endforeach
								</tbody>
							</table>
						@endif
					</div>
				</div>
			</div>
		</div>
	</form>
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