@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Payment Gateways')

@section('breadcrumbs')
	<a href="/settings/paymentgateways">Payment Gateways</a>
	<li class="breadcrumb-item active">Stripe</li>
@stop

@section('content')
	<form method="post">
		<input type="hidden" name="_token" value="{{csrf_token()}}">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Stripe Information</h3>
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
							<label for="secretkey">Secret Key: </label>
							<input type="text" name="secretkey" id="secretkey" class="form-control" value="{{old('secretkey', Settings::get('stripe.secretkey'))}}" required>
						</div>
						<div class="form-group">
							<label for="publishablekey">Publishable Key: </label>
							<input type="text" name="publishablekey" id="publishablekey" class="form-control" value="{{old('publishablekey', Settings::get('stripe.publishablekey'))}}" required>
						</div>
					</div>
					<div class="card-footer">
					<a href="/settings/paymentgateways"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
					<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"></i> Update Settings</button>
					<a href="/settings/paymentgateway/clear/stripe/"><button type="button" class="btn btn-danger float-right mr-2"> <i class="fa fa-arrow-fa-remove"></i> Clear</button></a>
				</div>
				</div>
			</div>
		</div>

	</form>
@stop
