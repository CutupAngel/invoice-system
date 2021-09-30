<div role="tabpanel" class="tab-pane {{ isset($paymentMethods['checked']) && $paymentMethods['checked'] == 'card' ? 'active' : '' }}" id="creditcard">
	<div class="row">
		<div class="col-md-12 directions">
			<p>{{ trans('frontend.chk-creditsub') }}</p>
			<span class="payment-errors"></span>
		</div>
		<div class="col-xs-12">
			<div class="row">
				@if ($savePaymentsAllowed)
					<div class="col-xs-6 float-right">
						<label class="check">
						<input type="checkbox" name="paymentMethod[ccsave]" value="1">
						<span class="checkmark"></span>
						{{ trans('frontend.chk-creditsave') }}
					</label>
					</div>
					<div class="col-xs-6 float-right">
						<label class="check">
						<input type="checkbox" name="paymentMethod[ccautocharge]" value="1">
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
		@else
			<div class="col-md-6 center-block">
				<input class="form-control tokenSkip" data-worldpay="number" type="text" placeholder="{{ trans('frontend.chk-creditcardnum') }}" name="paymentMethod[number]">
			</div>
			<div class="col-md-6 center-block">
				<input class="form-control tokenSkip" data-worldpay="name" type="text" placeholder="{{ trans('frontend.chk-creditname') }}" name="paymentMethod[cardname]">
			</div>
		@endif
	</div>
	<div class="row">
		<div class="col-md-4 center-block">
			<div class="input-prepend">
				<select id="ccMonth" data-worldpay="exp-month" class="form-control tokenSkip" name="paymentMethod[expiration][month]">
					<option>{{ trans('frontend.chk-expmonth') }}</option>
					@for ($index = 1; $index <= 12; $index++)
						<option value="{{ $index <= 9 ? '0' . $index : $index }}">{{ $index <= 9 ? '0' . $index : $index }}</option>
					@endfor
				</select>
			</div>
		</div>
		<div class="col-md-4 center-block">
			<div class="input-prepend">
				<select id="ccYear" data-worldpay="exp-year" class="form-control tokenSkip" name="paymentMethod[expiration][year]">
					<option>{{ trans('frontend.chk-expyear') }}</option>
					@for ($index = 2020; $index <= 2031; $index++)
						<option value="{{ $index }}">{{ $index }}</option>
					@endfor
				</select>
			</div>
		</div>
		<div class="col-md-4 center-block">
			<input class="form-control" data-worldpay="cvc" type="text" placeholder="{{ trans('frontend.chk-cvc') }}" name="paymentMethod[cvc]">
		</div>
	</div>
</div>
