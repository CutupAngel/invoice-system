@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Integrations')

@section('breadcrumbs')
	<li>Settings</li>
	<li><a href="/settings/integrations">Integrations</a></li>
	<li class="active">FraudLabs Pro Settings</li>
@stop

@section('content')

@if (count($errors) > 0)
	<div class="alert alert-dismissible alert-danger">
		<button type="button" class="close" data-dismiss="alert">×</button>
		@foreach ($errors->all() as $error)
			{{$error}}<br>
		@endforeach
	</div>
@endif

<form method="POST">
	<input type="hidden" name="_token" value="{{csrf_token()}}">
		<div class="row">
			<div class="col-sm-6">
					<div class="card">
						<div class="card-header">
							<h3 class="card-title">FruadLabs Pro Information</h3>
						</div>
						<div class="card-body">
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
								<label for="enable">Enable FraudLabs Pro? </label>
								<span class="checkbox">
									<label>
										<input type="checkbox" name="fraudlabs" value="1" {{ old('fraudlabs', Settings::get('integration.fraudlabs')) == false ?: 'checked' }}>
										Tick to enable FraudLabs Pro Fraud Checking for Orders
									</label>
								</span>
							</div>
							<div class="form-group">
								<label for="hostname">FraudLabs Pro API Key: </label>
								<input type="text" name="apiKey" id="apiKey" class="form-control" value="{{old('apiKey', Settings::get('fraudlabs.apiKey'))}}" required>
							</div>
							<div class="form-group">
								<label for="username">FraudLabs Pro Fraud Risk Score: </label>
								<input type="number" min="0" max="100" name="riskScore" id="riskScore" class="form-control" value="{{old('riskScore', Settings::get('fraudlabs.riskScore'))}}" required>
							</div>
							<div class="form-group">
								<label for="enable">Reject Free Email Service </label>
								<span class="checkbox">
									<label>
										<input type="checkbox" name="rejectFreeEmail" value="1" {{ old('rejectFreeEmail', Settings::get('fraudlabs.rejectFreeEmail')) == false ?: 'checked' }}>
										Block orders from free email addresses such as Hotmail & Yahoo!
									</label>
								</span>
							</div>
							<div class="form-group">
								<label for="enable">Reject Country Mismatch </label>
								<span class="checkbox">
									<label>
										<input type="checkbox" name="rejectCountryMismatch" value="1" {{ old('rejectCountryMismatch', Settings::get('fraudlabs.rejectCountryMismatch')) == false ?: 'checked' }}>
										Block orders where order address is different from IP Location
									</label>
								</span>
							</div>
							<div class="form-group">
								<label for="enable">Reject Anonymous Networks </label>
								<span class="checkbox">
									<label>
										<input type="checkbox" name="rejectAnonymousNetworks" value="1" {{ old('rejectAnonymousNetworks', Settings::get('fraudlabs.rejectAnonymousNetworks')) == false ?: 'checked' }}>
										Block orders where the user is ordering through an anonymous network
									</label>
								</span>
							</div>
							<div class="form-group">
								<label for="enable">Reject High Risk Country </label>
								<span class="checkbox">
									<label>
										<input type="checkbox" name="rejectHighRiskCountry" value="1" {{ old('rejectHighRiskCountry', Settings::get('fraudlabs.rejectHighRiskCountry')) == false ?: 'checked' }}>
										Block orders from high risk countries
									</label>
								</span>
							</div>
						</div>
						<div class="card-footer">
							<a href="/settings/integrations"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
							<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"></i> Update Settings</button>
						</div>
					</div>
			</div>
			<div class="col-sm-6">
				<div class="card">
					<div class="card-header">
						<div class="card-header">
							<h3 class="card-title">FruadLabs Pro Information</h3>
						</div>
						<div class="card-body">
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
								<label for="enable">Skip Fraud Check for Existing </label>
								<span class="checkbox">
									<label>
										<input type="checkbox" name="skipCheckExisting" value="1" {{ old('skipCheckExisting', Settings::get('fraudlabs.skipCheckExisting')) == false ?: 'checked' }}>
										Tick this box to skip the fraud check for existing clients who already have an active order
									</label>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
</form>
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
