<div role="tabpanel" class="tab-pane {{ isset($paymentMethods['checked']) && $paymentMethods['checked'] == 'stripe' ? 'active' : '' }}" id="stripecard">
	<div class="row">
		<div class="col-md-12 directions">
			<p>{{ trans('frontend.chk-creditsub') }}</p>
			<span class="payment-errors"></span>
		</div>
		@if(isset($stripeCards->data))
			<div class="col-md-12 directions">
				<label class="radioc">
					<input type="radio" name="select_card" value="new">
					<span class="checkmark"></span>
					<i class="fa fa-{{ trans('frontend.chk-stripeicon') }}"></i>
					<b><span>{{ trans('frontend.chk-newpaymentmethodstripe') }}</span></b>
				</label>
			</div>
		@endif
		<div class="col-xs-12">
			<div class="row">
				@if ($savePaymentsAllowed)
					<div class="col-xs-6 float-right">
						<label class="check">
						<input type="checkbox" name="paymentMethod[ccsave]" id="cc_save" value="1">
						<span class="checkmark"></span>
						{{ trans('frontend.chk-creditsave') }}
					</label>
					</div>
					<div class="col-xs-6 float-right">
						<label class="check">
						<input type="checkbox" name="paymentMethod[ccautocharge]" id="cc_autocharge" value="1">
						<span class="checkmark"></span>
						{{ trans('frontend.chk-creditautocharge') }}
					</label>
					</div>
				@endif
			</div>
		</div>
		@if ($paymentMethods['card'] && $paymentMethods['cardGateway'] == 'paypalpro')
			<div class="col-md-4 center-block">
				<input class="form-control tokenSkip" type="text" placeholder="{{ trans('frontend.chk-creditcardnum') }}" name="paymentMethod[number]">
			</div>
			<div class="col-md-4 center-block">
				<input class="form-control tokenSkip" type="text" placeholder="{{ trans('frontend.chk-creditname') }}" name="paymentMethod[cardname]">
			</div>
			<div class="col-md-4 center-block">
				<select id="ccType" class="form-control tokenSkip" {!! $paymentNames['tokenDataCCType'] !!}>
					<option value="visa">Visa</option>
					<option value="mastercard">MasterCard</option>
					<option value="discover">Discover</option>
					<option value="amex">American Express</option>
				</select>
			</div>
		@endif
	</div>
	<div class="row">
		<div class="col-md-12 center-block">
			<input class="form-control tokenSkip" type="text" placeholder="{{ trans('frontend.chk-creditname') }}" name="paymentMethod[cardname]" id="card-name">
		</div>

		<div class="col-md-12 center-block form-control" id="card-element" style="width:95.5%; margin-left:15px;">
				<!-- A Stripe Element will be inserted here. -->
		</div>
		@if(isset($stripeCards->data))
			<div class="col-md-12 directions">
				<label class="radioc">
					<input type="radio" id="select_card" name="select_card" value="existing">
					<span class="checkmark"></span>
					<i class="fa fa-{{ trans('frontend.chk-stripeicon') }}"></i>
					<b><span>{{ trans('frontend.chk-savedpaymentmethodstripe') }}</span></b>
				</label>
			</div>
			<div class="col-xs-12">
				<select class="form-control" name="saved_stripe_card" id="saved_stripe_card" onchange="SelectCard();">
					<option value="" selected>--Select Card--</option>
					@foreach($stripeCards->data as $stripecard)
						<option value="{{ $stripecard->id }}">{{ $stripecard->card->brand }} ****{{ $stripecard->card->last4 }}</option>
					@endforeach
				</select>
			</div>
		@endif
	</div>
		<div id="report_message" class="alert alert-success" style="display:none;">
		</div>
</div>
