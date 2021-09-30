@foreach($paymentMethods['offsite'] as $gateway)
<div role="tabpanel" class="tab-pane {{ isset($paymentMethods['checked']) && $paymentMethods['checked'] == 'offsite' ? 'active' : '' }}" id="{{ $gateway->name }}">
	<div class="row">
		<div class="col-md-12 directions">
			<span>{{ trans('frontend.chk-'.$gateway->name.'sub') }}</span>
		</div>
	</div>
</div>
@endforeach
