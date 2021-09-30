@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Payment Gateways')

@section('breadcrumbs')
	<a href="/settings/paymentgateways">Payment Gateways</a>
	<li class="breadcrumb-item active">WorldPay</li>
@stop

@section('content')
	<form method="post">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">WorldPay Information</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="liveServiceKey">Live Service Key: </label>
							<input type="text" name="liveServiceKey" id="liveServiceKey" class="form-control" value="{{ old('liveServiceKey', Settings::get('worldpay.liveServiceKey')) }}">
						</div>
						<div class="form-group">
							<label for="liveClientKey">Live Client Key: </label>
							<input type="text" name="liveClientKey" id="liveClientKey" class="form-control" value="{{ old('liveClientKey', Settings::get('worldpay.liveClientKey')) }}">
						</div>
						<div class="form-group">
							<label for="testServiceKey">Test Service Key: </label>
							<input type="text" name="testServiceKey" id="testServiceKey" class="form-control" value="{{ old('testServiceKey', Settings::get('worldpay.testServiceKey')) }}">
						</div>
						<div class="form-group">
							<label for="testClientKey">Test Client Key: </label>
							<input type="text" name="testClientKey" id="testClientKey" class="form-control" value="{{ old('testClientKey', Settings::get('worldpay.testClientKey')) }}">
						</div>
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="testmode" {{ empty(old('testmode', Settings::get('worldpay.testmode'))) ? '' : 'checked' }} value="1">
									Test Mode
								</label>
							</div>
						</div>
					</div>
					<div class="card-footer">
					<a href="/settings/paymentgateways"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
					<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"></i> Update Settings</button>
					<a href="/settings/paymentgateway/clear/worldpay/"><button type="button" class="btn btn-danger float-right mr-2"> <i class="fa fa-arrow-fa-remove"></i> Clear</button></a>
				</div>
				</div>
			</div>
		</div>

	</form>
@stop
