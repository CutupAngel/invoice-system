@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Payment Gateways')

@section('breadcrumbs')
	<a href="/settings/paymentgateways">Payment Gateways</a>
	<li class="breadcrumb-item active">PayPal Standard</li>
@stop

@section('content')
	<form method="post">
		<input type="hidden" name="_token" value="{{csrf_token()}}">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">PayPal Standard Information</h3>
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

						<div class="alert alert-info">
							<b>Note:</b> PayPal Email addresses are unable to be validated, please verify it is correct.
						</div>
						<div class="form-group">
							<label for="email">PayPal Email: </label>
							<input type="text" name="email" id="email" class="form-control" value="{{old('email', Settings::get('paypalstandard.email'))}}" required>
						</div>
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="testmode" {{ empty(old('testmode', Settings::get('paypalstandard.testmode'))) ? '' : 'checked' }} value="1">
									Test Mode
								</label>
							</div>
						</div>
					</div>
					<div class="card-footer">
					<a href="/settings/paymentgateways"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
					<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"></i> Update Settings</button>
					<a href="/settings/paymentgateway/clear/paypalstandard/"><button type="button" class="btn btn-danger float-right mr-2"> <i class="fa fa-arrow-fa-remove"></i> Clear</button></a>
				</div>
				</div>
			</div>
		</div>

	</form>
@stop
