<div id="paymentMethod">
	<div class="row">
		<div class="col-md-4">
			<h2>{{ trans('frontend.chk-paymentmth') }}</h2>
		</div>
		<div class="col-md-4 hidden-xs">

		</div>
		<div class="col-md-4 savePaymentMethod">

		</div>
	</div>
	<div class="row">
		<div class="col-md-12 directions">
			<p>{{ trans('frontend.chk-paymentmthsub') }}</p>
			<div class="clearfix"></div>

		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<ul class="nav nav-tabs" id="paymentMethodTabs">
				<?php $payment = ''; ?>
				@if (isset($paymentMethods['card']) && $paymentMethods['card'])
					<?php $payment = 'card'; ?>
					<li role="presentation" class="active">
						<a data-target="#creditcard" aria-controls="creditcard" role="tab">
							<label class="radioc">
							<input type="radio" {{ isset($paymentMethods['checked']) && $paymentMethods['checked'] == 'card' ? ' checked="checked"' : '' }} id="paymentMethod[type]" name="paymentMethod[type]" value="0">
							<span class="checkmark"></span>
							<i class="fa fa-credit-card"></i>
							<span class="hidden-xs">{{ trans('frontend.chk-credit') }}</span>
						</label>
						</a>
					</li>
					<input type="hidden" id="valuePaymentMethod" name="paymentMethod[type]" value="0" />
				@endif
				@if (isset($paymentMethods['stripe']) && $paymentMethods['stripe'])
					<?php $payment = 'stripe'; ?>
					<li role="presentation" class="active">
						<a data-target="#stripecard" aria-controls="stripecard" role="tab">
							<label class="radioc">
							<input type="radio" checked="checked" id="paymentMethod[type]" name="paymentMethod[type]" value="0">
							<span class="checkmark"></span>
							<i class="fa fa-credit-card"></i>
							<span class="hidden-xs">{{ trans('frontend.chk-credit') }}</span>
						</label>
						</a>
					</li>
					<input type="hidden" name="stripe" value="1" />
					<input type="hidden" id="valuePaymentMethod" name="paymentMethod[type]" value="0" />
				@endif
				@if (isset($paymentMethods['bank']) && $paymentMethods['bank'])
					<?php $payment = 'bank'; ?>
					<li role="presentation"{{ isset($paymentMethods['checked']) && $paymentMethods['checked'] == 'bank' ? ' class="active"' : '' }}>
						<a data-target="#bank" aria-controls="bank" role="tab">
							<label class="radioc">
							<input  type="radio"{{ isset($paymentMethods['checked']) && $paymentMethods['checked'] == 'bank' ? ' checked="checked"' : '' }} id="paymentMethod[type]" name="paymentMethod[type]" value="1">
							<span class="checkmark"></span>
							<i class="fa fa-bank"></i>
							<span class="hidden-xs">{{ trans('frontend.chk-bank') }}</span>
						</label>
						</a>
					</li>
					<input type="hidden" id="valuePaymentMethod" name="paymentMethod[type]" value="1" />
				@endif
				@if (isset($paymentMethods['banktransfer']) && $paymentMethods['banktransfer'])
					<?php $payment = 'bankTransfer'; ?>
					<li role="presentation" class="active">
						<a data-target="#banktrasfer" aria-controls="banktransfer" role="tab">
							<label class="radioc">
							<input type="radio" {{ isset($paymentMethods['checked']) && $paymentMethods['checked'] == 'bankTransfer' ? 'checked="checked"' : '' }} id="paymentMethod[type]" name="paymentMethod[type]" value="1">
							<span class="checkmark"></span>
							<i class="fa fa-bank"></i>
							<span class="hidden-xs" style="border:1px solid black;">{{ trans('frontend.chk-bank') }}</span>
						</label>
						</a>
					</li>
					<br>
						<textarea id="textarea_bank_info" class="form-control" style="height:300px; resize:none; overflow:hidden;" disabled>{{ Settings::get('banktransfer.information') ?: $bank_information }}</textarea>
						<!--<span class="hidden-xs">{{ trans('frontend.chk-banktransfersub') }}</span>-->
						<input type="hidden" id="valuePaymentMethod" name="paymentMethod[type]" value="1" />
				@endif
				@if ($paymentMethods['offsite'] && sizeof($paymentMethods['offsite']) > 0)
					@foreach($paymentMethods['offsite'] as $gateway)
						<li role="presentation" class="<?=  $payment != '' ? '' : 'active';  ?>" >
							<a data-target="#{{ $gateway->name }}" aria-controls="${{ $gateway->name }}" role="tab">
								<label class="radioc">
								<input type="radio" {{ $payment == '' ? ' checked="checked"' : '' }} id="paymentMethod[type]"  name="paymentMethod[type]" value="offsite.{{ $gateway->name }}">
								<span class="checkmark"></span>
								<i class="fa fa-{{ trans('frontend.chk-' . $gateway->name . 'icon') }}"></i>
								<span class="hidden-xs">{{ trans('frontend.chk-' . $gateway->name) }}</span>
								
							</label>
							</a>
						</li>
					
						
					<input type="hidden" id="valuePaymentMethod" name="paymentMethod[type]" value="offsite.{{ $gateway->name }}" />	
					@endforeach
				@endif
				
			</ul>
			<div class="tab-content">
				@if (isset($paymentMethods['card']) && $paymentMethods['card'] == 1)
					@include('Checkout.gateways.card.data')
				@endif
				@if (isset($paymentMethods['stripe']) && $paymentMethods['stripe'] == 1)
					@include('Checkout.gateways.card.stripe')
				@endif
				@if (isset($paymentMethods['bank']) && $paymentMethods['bank'] == 1)
					@include('Checkout.gateways.bank.data')
				@endif
				@if ($paymentMethods['offsite'] && sizeof($paymentMethods['offsite']) > 0)
					@include('Checkout.gateways.offsite.data')
				@endif
			</div>

		</div>
	</div>
</div>
