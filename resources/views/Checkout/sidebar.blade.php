<aside class="col-md-4 total-sidebar-ben">
    <ul class="totals">

        <li class="hitem">
            {{ trans('frontend.chk-yourorder') }}
        </li>
        @php $totalOptionCost = 0; @endphp
        @foreach($basketItems as $k=>$item)
            @php $totalItemCost = 0; @endphp
            <li class="total clearfix">
                <div class="float-left lab">
                    {{ $item->name }}
                    <p>x{{ $item->qty }}</p>
                </div>

                <div class="float-right">
                    @php
                        $price = 0;
                        if($item->options->trial == 0) $price = $item->price;
                    @endphp
                    <span class="amount">{!! $currency->symbol !!}{{ number_format($price / $default_currency->conversion * $currency->conversion, 2) }}</span>
                    @if (isset($item->options['fee']) && $item->options['fee'] > 0)
                        <h5>{{ 'Setup Fee' }}</h5>
                        <h5>
                            <strong>{!! $currency->symbol !!}{{ number_format(($item->options['fee'] / $default_currency->conversion * $currency->conversion), 2) }}</strong>
                        </h5>
                    @endif

                    @if(!empty($item->options))
                        @if($item->options->options)
                            <br/> Options: <br/>
                            @for($x = 0; $x < count($item->options->options); $x++)
                                @php
                                    $option = \App\Package_Options::where('id', $item->options->options[$x]['option_id'])
                                                                                                    ->first();
                                @endphp
                                @if($option->type != 0 && $item->options->options[$x]['value'] != "")
                                    ({{ $item->options->options[$x]['value'] }})
                                @endif
                                {{$item->options->options[$x]['display_name']}}
                                : {!! $currency->symbol !!}{{ $item->options->options[$x]['price'] / $default_currency->conversion * $currency->conversion }}
                                ,
                                Fee: {!! $currency->symbol !!}{{ number_format($item->options->options[$x]['fee'] / $default_currency->conversion * $currency->conversion, 2) }}
                                <br/>
                                @php
                                    if($option->type == 2) {
                                        $totalItemCost += ((int)$item->options->options[$x]['value'] * $item->options->options[$x]['price']) + $item->options->options[$x]['fee'];
                                    }
                                    else {
                                        $totalItemCost += $item->options->options[$x]['price'] + + $item->options->options[$x]['fee'];
                                    }
                                @endphp
                            @endfor
                        @endif
                    @endif
                    @php
                        $totalOptionCost += $totalItemCost;
                    @endphp
                </div>
            </li>
        @endforeach

        {{-- User Credit --}}
        @if(Auth::user())
            @if(intval($credit) > 0)
                <li class="total clearfix">
                    <div class="float-left lab">
                        Credit Balance
                    </div>
                    <div class="float-right" id="creditCustom">
                        {!! $currency->symbol !!} {{ number_format(intval($credit) / $default_currency->conversion * $currency->conversion, 2) }}
                    </div>
                    <br>
                    <label class="check"> Use Credit
                        <input type="checkbox" name="credit" id="useCredit">
                        <span class="checkmark"></span>
                    </label>
                </li>
            @endif
        @endif
        {{-- User Credit --}}

        <li class="total clearfix">
            <div class="float-left lab">
                {{ trans('frontend.chk-total') }}
            </div>
            <div class="float-right">
				<span class="amount" id="amountCustom">
				@if($fixedDiscountPercentage > 0)
                        {!! $currency->symbol !!}{{ number_format(($basketTotal * ((100 -$fixedDiscountPercentage) / 100)) / $default_currency->conversion * $currency->conversion, 2) }}
                    @else
                        {!! $currency->symbol !!}{{ number_format(($basketTotal / $default_currency->conversion * $currency->conversion), 2) }}
                    @endif
				</span>
            </div>
        </li>

        @php $taxAmount = 0; @endphp
        <input type="hidden" name="tax_percentage">
        @if (isset($taxes) && sizeof($taxes) > 0)
            @foreach ($taxes as $key => $tax)
                @php
                    
                @endphp
                <li class="total clearfix" id="taxtotal_{{ $key }}">
                    <div class="float-left lab">
                        {{ trans('frontend.chk-tax') }} (<span id="taxName">{{ $tax['name'] }}</span>%)
                    </div>
                    <div class="float-right">
                        <span id="taxAmount" class="amount">{!! $currency->symbol !!}
                            {{ 0.00 }}
                        </span>
                    </div>
                </li>
            @endforeach
        @endif

        <input type="hidden" id="currencySymbolHidden" value="{!! $currency->symbol !!}"/>

        @php
            if($fixedDiscountPercentage > 0)
                $totalDue = number_format(($basketGrendTotal * ((100 - $fixedDiscountPercentage) / 100)) / $default_currency->conversion * $currency->conversion, 2);
            else
                $totalDue = number_format($basketGrendTotal / $default_currency->conversion * $currency->conversion, 2);
        @endphp

        <input type="hidden" id="taxAmountHidden" name="taxAmountHidden" value="{{ number_format($taxAmount, 2) }}"/>

        <li class="total clearfix" id="grandTotal">
            <div class="float-left lab">
                {{ trans('frontend.chk-totaldue') }}
            </div>
            <div class="float-right">
				<span class="amount" id="totaldueCustom">
				{!! $currency->symbol !!}{{ $totalDue }}
				</span>
            </div>
        </li>
        <input type="hidden" id="totalDueHidden" value="{{ $totalDue }}"/>

        @if($fixedDiscountPercentage > 0)
            <li class="total clearfix" id="divDiscountFixed">
                <div class="float-left lab">
                    {{ trans('frontend.chk-discountfixed') }}
                    <div id="divDiscountFixedMessage" class="alert alert-success">Congratulations! You got
                        discount: {{ number_format($fixedDiscountPercentage, 0) }}%
                    </div>
                </div>
            </li>
        @endif

        @if(Settings::get('invoice.discountCode'))
            <li class="total clearfix" id="divDiscountCode">
                <div class="float-left lab">
                    {{ trans('frontend.chk-discountcode') }}
                    <div class="input-group mt-3 mb-3">
                        <input type="text" class="form-control" id="discountCode" name="discountCode"
                               value="{{ $discountCode }}" style="height:34px;">
                        <div class="input-group-append">
                            <button id="btnDiscountCode" type="button" class="btn btn-info">
                                <i class="fa fa-angle-right"></i>
                            </button>
                            <button id="btnRemoveDiscountCode" type="button" class="btn btn-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div id="divDiscountCodeMessage"></div>
                </div>
            </li>
        @endif
    </ul>

    @php
        $isPaymentMethodAvailable = (isset($paymentMethods['card']) && $paymentMethods['card']) || (isset($paymentMethods['stripe']) && $paymentMethods['stripe']) || (isset($paymentMethods['bank']) && $paymentMethods['bank']) || (isset($paymentMethods['banktransfer']) && $paymentMethods['banktransfer']) || (isset($paymentMethods['offsite']) && !empty($paymentMethods['offsite']));
    @endphp
    <div id="agreement" style="margin:10px 0;">
        <label for="term">{{ trans('frontend.chk-agreements') }}</label>
        <label class="check">
            <input type="checkbox" id="term" name="term" value="1"
                   required {{ $isPaymentMethodAvailable ? '' : 'disabled' }} /> I agree to these <a target="_blank"
                                                                                                     style=" color: #fff;"
                                                                                                     href="https://legal.storage.sbg1.cloud.baseserv.com/terms-conditions.html">Terms
                &amp; Conditions</a> & <a style=" color: #fff;"
                                          href="https://legal.storage.sbg1.cloud.baseserv.com/privacy-policy.html">Privacy
                Policy</a>
            <span class="checkmark"></span>
            <span class="agree-error"></span>
        </label>

    </div>
    <button type="button" id="frmCheckoutSubmit"
            class="btn btn-danger btn-block {{ $isPaymentMethodAvailable ? '' : 'disabled' }}">{{ trans('frontend.chk-confirm') }}</button>
</aside>

<div class="mb-3" id="card-message"></div>
<!-- Used to display form errors. -->
<div id="card-errors" role="alert"></div>
