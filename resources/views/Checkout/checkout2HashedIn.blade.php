@extends('Common.frontendLayout')
@section('title', 'Checkout')

@section('css')
	<link rel="stylesheet" href="https://v2.b-cdn.uk/dist/css/checkout2.css">
@stop

@section('content')
<div class="content">
<div class="center">
<div class="content-header">
        <h1>Checkout</h1>
</div><!-- content-header -->

	<!-- <div class="errMsgs" class="has-error"></div> -->

	<form id="frmCheckout" autocomplete="off">
		<div class="form-group">
			<div class="row">
				<div class="col-md-8">
					<div class="row">
						<div class="col-md-4 hidden-xs"></div>
						<div class="col-md-4"></div>
						<div class="col-md-4 hidden-xs"></div>
					</div>
					@if(empty($customer->password))
						<div id="accountInformation">
							<div class="row">
								<div class="col-md-4">
									<h2>{{ trans('frontend.chk-newsub') }}</h2>
								</div>
								<div class="col-md-8 hidden-xs">

								</div>
							</div>
							<div class="row">
								<div class="col-md-12 directions">
									<span>{{ trans('frontend.chk-newaccount') }}</span>
								</div>
							</div>
							<div class="row">
								<div class="col-md-4"><input class="form-control" type="text" name="account[username]" placeholder="{{ trans('frontend.chk-username') }}"/></div>
								<div class="col-md-4"><input class="form-control" type="password" name="account[password]" placeholder="{{ trans('frontend.chk-password') }}"/></div>
								<div class="col-md-4"><input class="form-control" type="password" name="account[password2]" placeholder="{{ trans('frontend.chk-cnfpassword') }}"/></div>
							</div>
						</div>
					@endif

					@include('Checkout.formBillingAddress')

					@if ((isset($paymentMethods['card']) && $paymentMethods['card']) || (isset($paymentMethods['bank']) && $paymentMethods['bank']) || (isset($paymentMethods['offsite']) && !empty($paymentMethods['offsite'])))
						@include('Checkout.payments')
					@else
						{{ trans('frontend.chk-no-paymentmethods') }}
					@endif
				</div>

				@include('Checkout.sidebar')
			</div>
		</div>
	</form>
</div><!-- center -->
</div><!-- content -->
@stop

@section('js')
	<script type="text/javascript">
		var csrf_token = '{{csrf_token()}}';
	</script>
	@if($tokenPayment)
		@include('Checkout.'.$user->getSetting('site.defaultGateway').'CheckoutJS')
	@else
		<script type="text/javascript">
			function startPayment()
			{
				submitData();
			}
		</script>
	@endif
	<script type="text/javascript" src="/dist/js/pages/checkout.js"></script>
@stop
