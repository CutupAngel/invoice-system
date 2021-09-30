@extends('Common.frontendLayout')
@section('title', 'Checkout')

@section('css')
    <link rel="stylesheet" href="https://v2.b-cdn.uk/dist/css/checkout.css">
    <style>
        .outer-section {
            max-width: 1100px;
            margin-top: 15px;
            margin-left: -5px;
        }
    </style>
@stop

@section('content')

@php
  $alertStyle = '';
  if(session()->has('payment_status')) {
    if(session()->get('payment_status') == 1) $alertStyle = 'success';
    else if(session()->get('payment_status') == 0) $alertStyle = 'danger';
  }

  $messageStyle = '';
  $message = '';
  if(session()->has('directadmin.out_of_qty')) {
    $message = session()->get('directadmin.out_of_qty');
    $messageStyle = 'danger';
    session()->forget('directadmin.out_of_qty');
  }
  if(session()->has('cpanel.out_of_qty')) {
    $message = session()->get('cpanel.out_of_qty');
    $messageStyle = 'danger';
    session()->forget('cpanel.out_of_qty');
  }
@endphp

@if ($alertStyle != '')
		<div class="alert alert-{{ $alertStyle }}">
				{{ session()->get('payment_message') }}
		</div>
@endif

@if ($messageStyle != '')
		<div class="alert alert-{{ $messageStyle }}">
				{{ $message }}
		</div>
@endif

<div class="jumbotron">
<div class="container">
<div class="content-header">
        <div id="print-area">
            <div class="row text-center">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <p>
                      {{ trans('frontend.rpt-welcome') }}<strong>
                    @if(!empty($user->billingContact->address))
                      {{$user->billingContact->address->email}}
                    @elseif(!empty($user->mailingContact->address))
                      {{$user->mailingContact->address->email}}
                    @elseif(!empty($user->adminContact->address))
                      {{$user->adminContact->address->email}}
                    @endif</strong>
                  </p>
                </div>
            </div>
            <hr />
            <div class="row ">
                <center>
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <h5>Client Details :</h5>
                    <h5>
						<strong>
    						@if ($invoice->address->contact_name)
    							{{ $invoice->address->contact_name }}
    						@else
    							{{ $customer->name }}
    						@endif
						</strong>
					</h5>
					@if (!empty($invoice->address->address_1))
						<h5>{{ $invoice->address->address_1 }}</h5>
					@endif

					@if(!empty($invoice->address->address_2))
						<h5>{{$invoice->address->address_2}}</h5>
					@endif

					@if(!empty($invoice->address->address_3))
						<h5>{{$invoice->address->address_3}}</h5>
					@endif

					@if(!empty($invoice->address->address_4))
						<h5>{{$invoice->address->address_4}}</h5>
					@endif

					<h5>{{$invoice->address->city}}, {{$invoice->address->county->name}} {{$invoice->address->postal_code}}</h5>
					<h5>{{$invoice->address->country->name}}</h5>
					@if($invoice->address->email)
						<h5><strong>Email: </strong>{{$invoice->address->email}}</h5>
					@else
						<h5><strong>Email: </strong>{{$customer->email}}</h5>
					@endif
					@if($invoice->address->phone)
						<h5><strong>Call: </strong>{{$invoice->address->phone}}</h5>
					@endif
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <h5>Payment Details :- {{ ($invoice->status) ? 'Paid' : 'Unpaid' }} {{$transaction->payment_method()}}</h5>
                    <h5><strong>Invoice No: </strong>{{$user->getSetting('invoice.prefix','')}}{{$invoice->invoice_number}}</h5>
                    <h5>Invoice Date:  {{date('jS M Y', strtotime($invoice->created_at))}}</h5>
                    <h5>Due Date: {{date('jS M Y', strtotime($invoice->due_at))}}</h5>
                    <h5>Paid On:  {{date('jS M Y')}}</h5>
                    <h5><strong>Amount {{ ($invoice->status) ? 'Paid' : 'Unpaid' }} : </strong>{!! $currency->symbol !!} {{ number_format($invoice->total,2) }}</h5>
                    <h5><strong>Credit Used : </strong>{!! $currency->symbol !!} {{ number_format($invoice->credit,2) }}</h5>
                </div>
                </center>
            </div>
            <hr />
            <br />
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalOptionCost = 0; @endphp
					                      @foreach ($invoice->items as $item)
                                  @php $totalItemCost = 0; @endphp
                                <tr>
                                    <td>{{ $item->item }}</td>
                                    <td>
                                      {{ $item->description }}
                                      @if(!empty($options))
                    										<br/> Options: <br/>
                    										@for($x = 0; $x < count($options); $x++)
                    											@php
                    												if($x > 0) echo '<br/>';
                    												$option = \App\Package_Options::find($options[$x]['option_id']);
                    											@endphp
                    											@if($option->type != 0 && $options[$x]['value'] != "")
                    												({{ $options[$x]['value'] }})
                    											@endif
                    											{{$options[$x]['display_name']}}: {!! $currency->symbol !!}
                                          {{ $options[$x]['price'] / $default_currency->conversion * $currency->conversion }}, Fee: {!! $currency->symbol !!}{{ number_format($options[$x]['fee'] / $default_currency->conversion * $currency->conversion, 2) }}
                                          @php
                    												if($option->type == 2) {
                    													$totalItemCost += ((int)$options[$x]['value'] * $options[$x]['price']) + $options[$x]['fee'];
                    												}
                    												else {
                    													$totalItemCost += $options[$x]['price'] + + $options[$x]['fee'];
                    												}
                    											@endphp
                    										@endfor
                    									@endif
                                      @php
                    			              $totalOptionCost += $totalItemCost;
                    			            @endphp
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{!! $currency->symbol !!}
                                    @if(isset($item->invoice->order->package) && $item->invoice->order->package->trial == 0)
                                      {{number_format($item->price / $default_currency->conversion * $currency->conversion, 2) }}
                                    @else
                                      0.00
                                    @endif
                                    {{ $currency->short_name }}</td>
                                    <td>{!! $currency->symbol !!}
                                      @if(isset($item->invoice->order->package) && $item->invoice->order->package->trial == 0)
                                        {{ number_format((($item->price * $item->quantity) + $totalOptionCost) / $default_currency->conversion * $currency->conversion, 2) }}
                                      @else
                                        0.00
                                      @endif
                                      {{$currency->short_name }}</td>
                                </tr>
				                        @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <hr />
            <div class="row">
                <div class="col-lg-9 col-md-9 col-sm-9" style="text-align: right; padding-right: 30px;">
                    <h5>Sub Total :</h5>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-3">
                    <h5><strong>{!! $currency->symbol !!} {{ number_format($subTotal / $default_currency->conversion * $currency->conversion,2) }} </strong></h5>
                </div>
        				@foreach($invoice->totals as $total)
                        <div class="col-lg-9 col-md-9 col-sm-9" style="text-align: right; padding-right: 30px;">
        					        <h5>
                            Tax :
                          </h5>
                        </div>
                        @if(Auth::User()->vat_number == '' || session()->get('use_tax') == 'yes')
                        <div class="col-lg-3 col-md-3 col-sm-3">
                            @if(session()->get('tax_amount') == '0.00')
                              <h5><strong>{!! $currency->symbol !!} {{ session()->get('tax_amount') }} </strong></h5>
                            @else
                              <h5><strong>{!! $currency->symbol !!} {{ number_format($total->price / $default_currency->conversion * $currency->conversion,2) }} </strong></h5>
                            @endif
                        </div>
                        @endif
        				@endforeach

                @if($invoice->credit > 0)
                <div class="col-lg-9 col-md-9 col-sm-9" style="text-align: right; padding-right: 30px;">
                    <h5>Credit :</h5>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-3">
                    <h5><strong>{!! $currency->symbol !!} - {{ number_format($invoice->credit / $default_currency->conversion * $currency->conversion,2) }} </strong></h5>
                </div>
                @endif

                <div class="col-lg-9 col-md-9 col-sm-9" style="text-align: right; padding-right: 30px;">
                    <h5>Total :</h5>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-3">
                    <h5><strong>{!! $currency->symbol !!} {{ number_format($invoice->total, 2) }} </strong></h5>
                </div>
            </div>
			@if (!empty($packages))
                <hr />
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                      <p>
    					 <strong>Your Package : </strong>
    					@foreach ($packages as $package)
    						{{ $package->name }}<br>
    					@endforeach
            </p>
    				</div>
                </div>
			@endif
			@if (!empty($files))
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
    					 <strong>Your Downloads : </strong>
    					@foreach ($packages as $package)
    						<a href="/products-ordered/order/{{ $invoice->order_id }}">{{ $filename }}</a><br>
    					@endforeach
    				</div>
                </div>
			@endif
        </div>
        <hr />
        <div class="row pad-bottom">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <a href="#" class="btn btn-primary ">Print Invoice</a>
                &nbsp;&nbsp;&nbsp;
                <a href="/products-ordered/order/{{ $invoice->order_id }}" class="btn btn-success">Download</a>

                <h5>Note: You can print and download the invoice by clicking above. </h5>
            </div>
        </div>
    </div>
	</div>
	</div>
@stop
