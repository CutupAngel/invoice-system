<i class="fa fa-shopping-cart" aria-hidden="true"></i>
&nbsp;&nbsp;
@if(isset($basketGrendTotal) && $basketGrendTotal > 0)
    <span id="grandTotalBasket">{!! $currency->symbol !!}{{ number_format($basketGrendTotal / $default_currency->conversion * $currency->conversion, 2) }}</span>
@else
    <span id="grandTotalBasket">{!! $currency->symbol !!}</span>
@endif
