@extends('Common.frontendLayout')
@section('title', 'Viewcart')
@section('content')
					<div class="row">
						<section class="content-header col-md-8 col-xs-12">
							<div class="row">
								<h2 class="col-xs-9">{{ trans('frontend.vc-shopping') }}</h2>
								<div class="col-xs-3 hitem">{{ trans('frontend.vc-price') }}</div>
							</div>
						</section>
						<section class="content col-md-8 col-xs-12">
								@foreach($cart['items'] as $k=>$item)
									<div class="row viewCartItem">
										<div class="col-xs-1">
											@if($mode == 'packages')
											<a href="/viewcart/delete/{{$k}}"><i class="fa fa-remove text-danger viewCartDelete" data-target="{{$k}}"></i></a>
											@endif
										</div>
										<div class="col-xs-8">
											<div class="title">{{ $item['desc'] }}</div>
										</div>
										<div class="col-xs-3 text-center">
											{{ $item['formattedAmount'] }}
										</div>
									</div>
								@endforeach
						</section>
						<aside class="total-sidebar col-md-4">
							<!--
								This will be where we keep track of the checkout step and possibly offer extra navigation
							-->
							<ul class="totals">
								<li class="hitem">
									{{ trans('frontend.vc-summary') }}
								</li>
								@foreach($cart['totals'] as $total)
									<li class="subtotal clearfix">
										<div class="float-left lab">
											{{ $total['desc'] }}
										</div>
										<div class="float-right">
											{{ $total['formattedAmount'] }}
										</div>
									</li>
								@endforeach
								<li class="subtotal clearfix" id="tax" style="display:none;">
								<div class="float-left lab">
								 {{ trans('frontend.vc-tax') }}
								</div>
								<div class="float-right">
									<span class="amount"></span>
								</div>
							</li>
							<li class="total clearfix" id="grandTotal">
								<div class="float-left lab">
									{{ trans('frontend.vc-total') }}
								</div>
								<div class="float-right">
									<span class="amount">{{ $cart['formattedGrandTotal'] }}</span>
								</div>
							</li>
							@foreach($cart['termTotals'] as $k=>$cycle)
							@if($k != 'One-Off' && $cycle['amount'] > 0)
								<li class="total clearfix">
									<div class="float-left lab">
										{{ trans('frontend.vc-tax') }} {{$k}}
									</div>
									<div class="float-right">
										<span class="amount">{{ $cycle['formattedAmount'] }}</span>
									</div>
								</li>
							@endif
							@endforeach
								<li class="buttons">
									<a href="/checkout"><button type="button" id="frmCheckout">{{ trans('frontend.vc-checkout') }}</button></a>
								</li>
						</aside>
					</div>
@stop
