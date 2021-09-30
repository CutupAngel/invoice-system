@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Payment Gateways')

@section('breadcrumbs')
	<a href="/settings/paymentgateways">Payment Gateways</a>
	<li class="breadcrumb-item active">PayPal Pro</li>
@stop

@section('content')
	<form method="post">
		<input type="hidden" name="_token" value="{{csrf_token()}}">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">PayPal Pro Information</h3>
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
							<label for="vendor">Vendor: </label>
							<input type="text" name="vendor" id="vendor" class="form-control" value="{{old('vendor', Settings::get('paypalpro.vendor'))}}" required>
						</div>
						<div class="form-group">
							<label for="user">User: </label>
							<input type="text" name="user" id="user" class="form-control" value="{{old('user', Settings::get('paypalpro.user'))}}" required>
						</div>
						<div class="form-group">
							<label for="password">Password: </label>
							<input type="text" name="password" id="password" class="form-control" value="{{old('password', Settings::get('paypalpro.password'))}}" required>
						</div>
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="testmode" {{ old('testmode', Settings::get('paypalpro.testmode')) === null ?: 'checked' }} value="1">
									Sandbox Mode
								</label>
							</div>
						</div>
					</div>
					<div class="card-footer">
					<a href="/settings/paymentgateways"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
					<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"></i> Update Settings</button>
					<a href="/settings/paymentgateway/clear/paypalpro/"><button type="button" class="btn btn-danger float-right mr-2"> <i class="fa fa-arrow-fa-remove"></i> Clear</button></a>
				</div>
				</div>
			</div>
		</div>

	</form>
@stop
