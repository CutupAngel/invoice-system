@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Payment Gateways')

@section('breadcrumbs')
	<a href="/settings/paymentgateways">Payment Gateways</a>
	<li class="breadcrumb-item active">2Checkout</li>
@stop

@section('content')
	<form method="post" id="2checkoutForm">
		<input type="hidden" name="_token" value="{{csrf_token()}}">
		<input type="hidden" name="2CheckoutToken">
		<div class="row">
			<div class="col-sm-4">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">2Checkout API Information</h3>
					</div>
					<div class="card-body" id="2checkoutBody">
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
							<label for="username">API Username: </label>
							<input type="text" name="username" id="username" class="form-control" value="{{old('username', Settings::get('2checkout.username'))}}" required>
						</div>
						<div class="form-group">
							<label for="password">API Password: </label>
							<input type="password" name="password" id="password" class="form-control" value="{{old('password', Settings::get('2checkout.password'))}}" required>
						</div>
						<div class="form-group">
							<label for="publishablekey">Publishable Key: </label>
							<input type="text" name="publishablekey" id="publishablekey" class="form-control" value="{{old('publishablekey', Settings::get('2checkout.publishablekey'))}}" required>
						</div>
						<div class="form-group">
							<label for="privatekey">Private Key: </label>
							<input type="text" name="privatekey" id="privatekey" class="form-control" value="{{old('privatekey', Settings::get('2checkout.privatekey'))}}" required>
						</div>
						<div class="form-group">
							<label for="sellerid">Seller ID/Account Number: </label>
							<input type="text" name="sellerid" id="sellerid" class="form-control" value="{{old('sellerid', Settings::get('2checkout.sellerid'))}}" required>
						</div>
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="testmode" {{ old('testmode', Settings::get('2checkout.testmode')) === null ? '' : 'checked' }} value="1">
									Sandbox Mode
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-8">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Help</h3>
					</div>
					<div class="card-body">
						<ul>
							<li><b>How do I get a API Username and Password?</b>
								<p>
									For live: <a href="http://help.2checkout.com/articles/FAQ/How-to-create-an-API-only-Username">2Checkout FAQ</a><br>
									For Sandbox: Sign in to the sandbox > Account > User Management > Create Username > Check API.
								</p>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<a href="/settings/paymentgateways"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
		<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"> Update Settings</i></button>
		<a href="/settings/paymentgateway/clear/2checkout/"><button type="button" class="btn btn-danger float-right"> <i class="fa fa-arrow-fa-remove"></i> Clear</button></a>
	</form>
@stop

@section('javascript')
	<script src='https://www.2checkout.com/checkout/api/2co.min.js'></script>
	<script>
		validated = false;
		$('#2checkoutForm').on('submit', function() {
			if (validated) {
				return true;
			} else {
				var publishableKey = $('#publishablekey').val(),
					sellerId = $('#sellerid').val(),
					api;

				api = $('input[name="testmode"]').prop('checked') ? 'sandbox' : 'production';

				TCO.loadPubKey(api, function() {
					TCO.requestToken(checkoutSuccess, checkoutFail, {
						sellerId: sellerId,
						publishableKey: publishableKey,
						ccNo: '4000000000000001',
						cvv: '123',
						expMonth: '12',
						expYear: '16'
					});
				});

				return false;
			}
		});

		function checkoutSuccess(data) {
			$('#keyCheck').fadeOut();
			if ($('#2checkoutBody .alert ul li').length === 0) {
				$('#2checkoutBody .alert').fadeOut();
			}

			$('input[name="2CheckoutToken"]').val(data.response.token.token);

			validated = true;
			$('#2checkoutForm').submit();
		}

		function checkoutFail(err) {
			if ($('#2checkoutBody .alert').length === 0) {
				$('#2checkoutBody').prepend(
					$('<div>')
						.html('<ul>')
						.addClass('alert alert-danger')
				);
			}

			$('#2checkoutBody .alert ul').append($('<li>').text('Invalid Publishable Key or Invalid Seller Id').attr('id', 'keyCheck'));
		}
	</script>
@stop
