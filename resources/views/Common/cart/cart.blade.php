@php $totalOptionCost = 0; @endphp
@if ($basketItems && sizeof($basketItems) > 0)
<table class="table table-hover" data-url="{{ route('update-item') }}">
    <thead>
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th class="text-center">{{ trans('frontend.vc-price') }}</th>
            <th class="text-center">Total</th>
            @if ($mode == 'packages')
            	<th> </th>
			@endif
        </tr>
    </thead>
    <tbody>
		@foreach($basketItems as $item)
      @php $totalItemCost = 0; @endphp
        <tr>
			<td class="col-sm-8 col-md-6">
				<div class="media">

					<div class="media-body">
						<h4 class="media-heading"><a href="#">{{ $item->name }}</a></h4>

						@if(!empty($item->options))
              @if($item->options->options)
    						<ul>
    							@for($x = 0; $x < count($item->options->options); $x++)
    								<li>
                    @php
                      $option = \App\Package_Options::where('id', $item->options->options[$x]['option_id'])
                                                      ->first();
                    @endphp
                    @if($option->type != 0 && $item->options->options[$x]['value'] != "")
                      ({{ $item->options->options[$x]['value'] }})
                    @endif
    								{{$item->options->options[$x]['display_name']}}: {!! $currency->symbol !!}{{ $item->options->options[$x]['price'] / $default_currency->conversion * $currency->conversion }}, Fee: {!! $currency->symbol !!}{{ number_format($item->options->options[$x]['fee'] / $default_currency->conversion * $currency->conversion, 2) }}
                    @php
                      if($option->type == 2 && $item->options->trial == 0) {
                        $totalItemCost += ((int)$item->options->options[$x]['value'] * $item->options->options[$x]['price']) + $item->options->options[$x]['fee'];
                      }
                      else {
                        if($item->options->trial == 0) {
                          $totalItemCost += $item->options->options[$x]['price'] + + $item->options->options[$x]['fee'];
                        }
                      }
                    @endphp
                  </li>
    							@endfor
    						</ul>
              @endif
						@endif
            @php
              $totalOptionCost += $totalItemCost;
            @endphp

					</div>
				</div>

			</td>
			<td class="col-sm-1 col-md-1 text-center">
				<input class="ajax-quantity" type="number" name="qty" value="{{ $item->qty }}" data-rowid="{{ $item->rowId }}">
			</td>
			<td class="col-sm-1 col-md-2 text-center"><strong>
        @php
          $price = 0;
          if($item->options->trial == 0) $price = $item->price;
        @endphp
				{!! $currency->symbol !!}{{ number_format(($price + $totalOptionCost) / $default_currency->conversion * $currency->conversion, 2) }}</strong>
				@if (isset($item->options['fee']) && $item->options['fee'] > 0)
					<h5>{{ 'Setup Fee' }}</h5>
					<h5><strong>{!! $currency->symbol !!}{{ number_format($item->options['fee'] / $default_currency->conversion * $currency->conversion, 2) }}</strong></h5>
				@endif
			</td>
			<td class="col-sm-1 col-md-2 text-center">
				@if (isset($item->options['fee']) && $item->options['fee'] > 0)
					<strong>{!! $currency->symbol !!}{{ number_format(($price + $totalOptionCost + $item->options['fee']) / $default_currency->conversion * $currency->conversion * $item->qty, 2) }}</strong>
				@else
					<strong>{!! $currency->symbol !!}{{ number_format(($price + $totalOptionCost) / $default_currency->conversion * $currency->conversion, 2) }}</strong>
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
    	<td>   </td>
        <td>   </td>
        <td>   </td>
		<td><h5>{{ trans('frontend.chk-total') }}</h5></td>
		<td class="text-right">
			<h5><strong>{!! $currency->symbol !!}{{ number_format($basketTotal / $default_currency->conversion * $currency->conversion, 2) }}</strong></h5>
		</td>
	</li>

    @if (isset($taxes) && sizeof($taxes) > 0)
    	@foreach ($taxes as $tax)
	        <tr>
	            <td>   </td>
	            <td>   </td>
	            <td>   </td>
	            <td><h5>{{ trans('frontend.chk-tax') }} ({{ $tax['name'] }}%)</h5></td>
	            <td class="text-right">
					<h5><strong>{!! $currency->symbol !!}{{ number_format($tax['tax'] / $default_currency->conversion * $currency->conversion, 2) }}</strong></h5>
				</td>
	        </tr>
        @endforeach
    @endif

    <tr>
        <td>   </td>
        <td>   </td>
        <td>   </td>
        <td><h5>{{ trans('frontend.vc-total') }}</h5></td>
        <td class="text-right">
			<h5><strong>{!! $currency->symbol !!}{{ number_format($basketGrendTotal / $default_currency->conversion * $currency->conversion, 2) }}</strong></h5>
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
		width: 90%;
		font-weight: 500;
		text-align: center;
		font-size: 16px;
	}
</style>
