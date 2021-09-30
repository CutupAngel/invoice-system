@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Payment Gateways')

@section('breadcrumbs')
	<a href="/settings/paymentgateways">Payment Gateways</a>
	<li class="breadcrumb-item active">PYMT Pro</li>
@stop

@section('content')
	<form method="post">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">PYMT Information</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="liveServiceKey">Token: </label>
							<input type="text" name="token" id="token" class="form-control" value="{{ old('token', Settings::get('pymtpro.token')) }}">
						</div>
						<div class="form-group">
							<label for="liveClientKey">Secret: </label>
							<input type="text" name="secret" id="secret" class="form-control" value="{{ old('secret', Settings::get('pymtpro.secret')) }}">
						</div>
						<div class="form-group">
							<label for="coin">Coin: </label>
							<select name="coin">
								<option value="ion" {{ (old('coin', Settings::get('pymtpro.coin')) == 'ion') ? 'selected' : '' }}>ion</option>
								<option value="btc" {{ (old('coin', Settings::get('pymtpro.coin')) == 'btc') ? 'selected' : '' }}>btc</option>
							</select>
						</div>
						<div class="form-group">
							<label for="testmode">Test Mode: </label>
							<select name="testmode">
								<option value="1" {{ (old('testmode', Settings::get('pymtpro.testmode')) == '1') ? 'selected' : '' }}>Enabled</option>
								<option value="0" {{ (old('testmode', Settings::get('pymtpro.testmode')) == '0') ? 'selected' : '' }}>Disabled</option>
							</select>
						</div>
					</div>
					<div class="card-footer">
					<a href="/settings/paymentgateways"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
					<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"></i> Update Settings</button>
					<a href="/settings/paymentgateway/clear/pymtpro/"><button type="button" class="btn btn-danger float-right mr-2"> <i class="fa fa-arrow-fa-remove"></i> Clear</button></a>
				</div>
				</div>
			</div>
		</div>

	</form>
@stop
