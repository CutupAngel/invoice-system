@if ($basketItems && sizeof($basketItems) > 0)
<table class="table table-hover" data-url="{{ route('update-item') }}">
    <thead>
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th class="text-center">{{ trans('frontend.vc-price') }}</th>
            <th class="text-center">Total</th>
            @if ($mode == 'packages')
            	<th> </th>
			@endif
        </tr>
    </thead>
    <tbody>
		@foreach($basketItems as $item)
        <tr>
			<td class="col-sm-8 col-md-6">
				<div class="media">
				
					<div class="media-body">
						<h4 class="media-heading"><a href="#">{{ $item->name }}</a></h4>
						{{--
						@if(!empty($item['options']))
						<ul>
							@foreach($item['options'] as $k2=>$option)
								<li>
								{{$option['name']}}:{{$option['desc']}} ({!! $currency->symbol !!}{{ $option['setup'] }})
								</li>
							@endforeach
						</ul>
						@endif
						--}}
					</div>
				</div>
				
			</td>
			<td class="col-sm-1 col-md-1 text-center">
				<input class="ajax-quantity" type="number" name="qty" value="{{ $item->qty }}">
			</td>
			<td class="col-sm-1 col-md-2 text-center"><strong>
				{!! $currency->symbol !!}{{ number_format($item->price, 2) }}</strong>
				@if (isset($item->options['fee']) && $item->options['fee'] > 0)
					<h5>{{ 'Setup Fee' }}</h5>
					<h5><strong>{!! $currency->symbol !!}{{ number_format($item->options['fee'], 2) }}</strong></h5>
				@endif
			</td>
			<td class="col-sm-1 col-md-2 text-center">
				@if (isset($item->options['fee']) && $item->options['fee'] > 0)
					<strong>{!! $currency->symbol !!}{{ number_format($item->subtotal + ($item->options['fee'] * $item->qty), 2) }}</strong>
				@else
					<strong>{!! $currency->symbol !!}{{ number_format($item->subtotal, 2) }}</strong>
				@endif
			</td>
			<td class="col-sm-1 col-md-1">
				@if ($mode == 'packages')
					<button type="button" class="btn button-2">
						<a href="javascript:;" class="remove-item" data-rowid="{{ $item->rowId }}">
							<i class="fa fa-remove viewCartDelete" data-target="{{ $item->id }}"></i>
						</a>
					</button>
				@endif
			</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>

    <tr>
    	<td>   </td>
        <td>   </td>
        <td>   </td>
		<td><h5>{{ trans('frontend.chk-total') }}</h5></td>
		<td class="text-right">
			<h5><strong>{!! $currency->symbol !!}{{ ($basketTotal + $totalSetupFee, 2) }}</strong></h5>
		</td>
	</li>

    @if (isset($totalTax) && $totalTax > 0)
        <tr>
            <td>   </td>
            <td>   </td>
            <td>   </td>
            <td><h5>{{ trans('frontend.chk-tax') }}</h5></td>
            <td class="text-right">
				<h5><strong>{!! $currency->symbol !!}{{ number_format($totalTax, 2) }}</strong></h5>
			</td>
        </tr>
    @endif

    <tr>
        <td>   </td>
        <td>   </td>
        <td>   </td>
        <td><h5>{{ trans('frontend.vc-total') }}</h5></td>
        <td class="text-right">
			<h5><strong>{!! $currency->symbol !!}{{ number_format($basketGrendTotal, 2) }}</strong></h5>
		</td>
    </tr>

	<tr>
		<td colspan="5">
			<a href="/checkout">
				<button type="button" class="btn btn-success pull-right" id="frmCheckout">{{ trans('frontend.vc-checkout') }}</button>
			</a>
		</td>
	</tr>

    </tfoot>
</table>
@else
	<h2><center>Cart is empty</center></h2>
@endif

<style>
	input.ajax-quantity {
		border: 0px;
		width: 30%;
		font-weight: 500;
		text-align: center;
		font-size: 16px;
	}
</style>