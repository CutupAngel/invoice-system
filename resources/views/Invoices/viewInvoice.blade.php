@extends ('Common.template')

@if($invoice->estimate)
	@section('page.title', 'View Estimate')
	@section('page.subtitle')
		{{ $invoice->invoice_number }}
	@stop
	@section('title')
	Invoices :: {{ $invoice->invoice_number }}
	@stop
	@section('breadcrumbs')
		<li class="active">View Estimate</li>
	@stop
@else
	@section('page.title', 'View Invoice')
	@section('page.subtitle')
		{{ $invoice->user->getSetting('invoice.prefix') }}{{ $invoice->invoice_number }}
	@stop
	@section('title')
	Invoices :: {{ $invoice->user->getSetting('invoice.prefix') }}{{ $invoice->invoice_number }}
	@stop
	@section('breadcrumbs')
		<li class="active">View Invoice</li>
	@stop
@endif

@section('content')

@if (session('status'))
		<div class="alert alert-success">
				{{ session('status') }}
		</div>
@endif

@if (session('has_error'))
	<div class="alert alert-danger">
		{{ session('has_error') }}
	</div>
@endif

	<section class="invoice payment-view p-3 mb-3">
		<div class="row">
			<div class="col-12">
				<h2 class="page-header">
					<i class="fa fa-globe"></i> {{ $invoice->user->siteSettings('name') }} @if ($invoice->estimate) Estimate @endif
					<small class="float-right">Date: {{ date('d/m/Y', strtotime($invoice->created_at)) }}</small>
				</h2>
			</div>
		</div>
		<div class="row invoice-info">
			<div class="col-sm-4 invoice-col">
				From
				<address>
					<strong>{{ $invoice->user->mailingContact->address->contact_name }}</strong><br>
					<div>{{ $invoice->user->mailingContact->address->address_1 }}</div>
					<div>{{ $invoice->user->mailingContact->address->address_2 }}</div>
					<div>{{ $invoice->user->mailingContact->address->address_3 }}</div>
					<div>{{ $invoice->user->mailingContact->address->address_4 }}</div>
					<div>
						{{ $invoice->user->mailingContact->address->city }},
						{{ @$invoice->user->mailingContact->address->county->name }}
						{{ $invoice->user->mailingContact->address->postal_code }}
					</div>
					<div>{{ @$invoice->user->mailingContact->address->country->name }}</div>
					<div>{{ $invoice->user->mailingContact->address->phone }}</div>
					<div>{{ $invoice->user->mailingContact->address->fax }}</div>
					<div>{{ $invoice->user->mailingContact->address->email }}</div>
				</address>
			</div>
			<div class="col-sm-4 invoice-col">
				<address>
					<strong>{{ $invoice->address->contact_name }}</strong><br>
					<div>{{ $invoice->address->address_1 }}</div>
					<div>{{ $invoice->address->address_2 }}</div>
					<div>{{ $invoice->address->address_3 }}</div>
					<div>{{ $invoice->address->address_4 }}</div>
					<div>
						{{ $invoice->address->city }},
						{{ @$invoice->address->county->name }}
						{{ $invoice->address->postal_code }}
					</div>
					<div>{{ @$invoice->address->country->name }}</div>
					<div>{{ $invoice->address->phone }}</div>
					<div>{{ $invoice->address->fax }}</div>
					<div>{{ $invoice->address->email }}</div>
				</address>
			</div>
			<div class="col-sm-4 invoice-col">
				<b>Payment Due:</b> <span>{{ date('d/m/Y', strtotime($invoice->due_at)) }}</span><br>
				@foreach ($invoice->transactions as $transaction)
				<b>Paid on:</b> <span>{{ date('d/m/Y', strtotime($transaction->created_at)) }}</span><br>
				@endforeach
				<b>Status:</b>
				@if ((int)$invoice->status === 0)
					<span>Unpaid</span>
				@elseif ((int)$invoice->status === 1)
					<span>Paid</span>
				@elseif ((int)$invoice->status === 2)
					<span>Overdue</span>
				@elseif ((int)$invoice->status === 3)
					<span>Refunded</span>
				@elseif ((int)$invoice->status === 4)
					<span>Canceled</span>
				@endif

			</div>
		</div>

		<div class="row">
			<div class="col-xs-12 table-responsive">
				<table class="table table-striped" id="items">
					<thead>
						<tr>
							<th class="col-xs-1">Item</th>
							<th class="col-xs-8">Description</th>
							<th></th>
							<th class="col-xs-1">Price</th>
							<th class="col-xs-1">Qty</th>
							<th></th>
							<th class="col-xs-1">Subtotal</th>
						</tr>
					</thead>
					<tbody>
						@php $totalOptionCost = 0; @endphp
						@forelse($invoice->items as $item)
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
											{{$options[$x]['display_name']}}: {!! $currency->symbol !!}{{ $options[$x]['price'] / $default_currency->conversion * $currency->conversion }}, Fee: {!! $currency->symbol !!}{{ number_format($options[$x]['fee'] / $default_currency->conversion * $currency->conversion, 2) }}
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
								<td class="text-right">{!! $currency->symbol !!}</td>
								<td>{{ number_format($item->price,2) }}</td>
								<td>{{ $item->quantity }}</td>
								<td class="text-right">{!! $currency->symbol !!}</td>
								<td>{{ number_format(($item->price * $item->quantity) + $totalOptionCost, 2) }}</td>
							</tr>
						@empty
							<td colspan="6">No items were invoiced.</td>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>

		<div class="row">
			<div class="col-6">
				<p class="lead">
					Payment Methods:<br>
					<img src="/dist/img/credit/visa.png" alt="Visa">
					<img src="/dist/img/credit/mastercard.png" alt="Mastercard">
					<img src="/dist/img/credit/american-express.png" alt="American Express">
					<img src="/dist/img/credit/paypal2.png" alt="Paypal">
				</p>

				<pre>
					{{ $invoice->comments }}
				</pre>
			</div>

			<div class="col-6">
				<div class="table-responsive">
					<table class="table" id="totals">
						<tr>
							<th>Subtotal:</th>
							<td class="text-right">{!! $currency->symbol !!}</td>
							<td id="subtotal">{{ number_format($invoice->subtotal, 2) }}</td>
						</tr>
						@if ($invoice->leftToPay !== $invoice->subtotal || $invoice->leftToPay === 0)
							<tr>
								<th>Left to pay:</th>
								<td class="text-right">{!! $currency->symbol !!}</td>
								<td id="subtotal">{{ number_format($invoice->leftToPay, 2) }}</td>
							</tr>
						@endif
						<tr>
							<th>Credit:</th>
							<td class="text-right">{!! $currency->symbol !!}</td>
							<td id="subtotal">- {{ number_format($invoice->credit, 2) }}</td>
						</tr>
						<tr>
							<th>Tax:</th>
							<td class="text-right">{!! $currency->symbol !!}</td>
							<td id="total">{{ number_format($invoice->tax, 2) }}</td>
						</tr>
						<tr>
							<th>Total:</th>
							<td class="text-right">{!! $currency->symbol !!}</td>
							<td id="total">{{ number_format($invoice->total + $invoice->tax, 2) }}</td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<div class="row no-print">
			<div class="col-12 float-right">
				<a href="/admin/invoices/{{ $invoice->id }}/pdf" target="_blank">
					<button id="btnPay" type="button" class="btn btn-success float-right ml-2">
						<i class="fa fa-file-pdf-o"></i> Download PDF
					</button>
				</a>
				@if(Auth::User()->isAdmin() || Auth::User()->isStaff() || Auth::User()->isClient())
				<a href="/admin/invoices/{{ $invoice->id }}/send_invoice_email">
					<button id="btnSendInvoice" type="button" class="btn btn-primary float-right">
						<i class="fa fa-mail-o"></i> Send Invoice to email
					</button>
				</a>
					<button type="button" class="btn btn-primary float-right mr-2" data-toggle="modal" data-target="#addPayment">
						<i class="fa fa-mail-o"></i> Add Payment
					</button>
				@endif
			</div>
		</div>

		<div class="row mt-5">
			<div class="col-xs-12 table-responsive">
				<table class="table table-striped" id="transactions">
					<thead>
					<tr>
						<th class="col-xs-1">Transaction Date</th>
						<th class="col-xs-8">Payment Method</th>
						<th class="col-xs-1">Transaction ID</th>
						<th></th>
						<th class="col-xs-1">Amount</th>
					</tr>
					</thead>
					<tbody>
					@foreach ($invoice->transactions as $transaction)
						<tr>
							<td>{{ date('d/m/Y', strtotime($transaction->created_at)) }}</td>
							<td>{{ $transaction->payment_method() }}</td>
							<td>{{ $transaction->transaction_id }}</td>
							<td class="text-right">&pound;</td>
							<td>{{ number_format($transaction->amount, 2) }}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
		</div>

		@if(($invoice->status === 0 || $invoice->status === 2) && !$user->isImpersonating())
		<div class="row no-print">
			<div class="col-xs-12">
				<a href="https://{{Config('app.site')->domain}}/invoices/{{$invoice->id}}/pay/">
					<button id="btnPay" type="button" class="btn btn-success float-right">
						<i class="fa fa-check"></i> Pay Invoice
					</button>
				</a>
			</div>
		</div>
		@endif
	</section>
@stop

@section('javascript')
	<div class="modal fade" id="addPayment" tabindex="-1" role="dialog" aria-labelledby="addPayment" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form action="{{ route('add_manual_payment') }}" method="post">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Enter Payment</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">

						{{ csrf_field() }}
						<p>You're entering a payment for Invoice: <strong>{{ $invoice->invoice_number }}</strong></p>

						<input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
						<div class="form-group">
							<label>Date Paid:</label>
							<div class="input-group date" id="reservationdate" data-target-input="nearest">
								<input type="text" name="date_paid"
									   class="form-control datetimepicker-input" data-target="#reservationdate" value="{{ old('date_paid') }}">
								<div class="input-group-append" data-target="#reservationdate"
									 data-toggle="datetimepicker">
									<div class="input-group-text"><i class="fa fa-calendar"></i></div>
								</div>
							</div>
							@if ($errors->first('date_paid'))
								<small class="text-danger">{{ $errors->first('date_paid') }}</small>
							@endif
						</div>
						<div class="form-group">
							<label>Transaction ID:</label>
							<input type="text" class="form-control" id="transactionId" name="transaction_id"
								   placeholder="Transaction ID" autocomplete="off" value="{{ old('transaction_id') }}">
							@if ($errors->first('transaction_id'))
								<small class="text-danger">{{ $errors->first('transaction_id') }}</small>
							@endif
						</div>
						<div class="form-group">
							<label>Status:</label>
							<select class="form-control" id="status" name="status">
								<option value="1" @if (!empty(old('status')) && old('status') == 1) selected @endif >
									Paid
								</option>
								<option value="0"
										@if ((int)$invoice->status === 1) disabled @endif
										@if (!empty(old('status')) && old('status') == 0) selected @endif >
									Partial Payment
								</option>
								<option value="2" @if (!empty(old('status')) && old('status') == 2) selected @endif >
									Due
								</option>
								<option value="3" @if (!empty(old('status')) && old('status') == 3) selected @endif >
									Refund
								</option>
								<option value="4" @if (!empty(old('status')) && old('status') == 4) selected @endif >Bad
									Debt
								</option>
							</select>
							@if ($errors->first('status'))
								<small class="text-danger">{{ $errors->first('status') }}</small>
							@endif
						</div>
						<div class="form-group">
							<label>Paid {!! $currency->symbol !!}:</label>
							<input type="text" class="form-control" id="amount" name="amount"
								   placeholder="{!! $currency->symbol !!}" autocomplete="off" value="{{ old('amount') }}">
							@if ($errors->first('amount'))
								<small class="text-danger">{{ $errors->first('amount') }}</small>
							@endif
						</div>
						<div class="form-group">
							<label>Payment Method:</label>
							<select class="form-control" id="paymentMethod" name="payment_method">
								<option value="0" @if (old('payment_method') == 0) selected @endif>Credit/Debit Card</option>
								<option value="1" @if (old('payment_method') == 1) selected @endif>Bank Transfer</option>
								<option value="3" @if (old('payment_method') == 3) selected @endif>Other</option>
							</select>
							@if ($errors->first('payment_method'))
								<small class="text-danger">{{ $errors->first('payment_method') }}</small>
							@endif
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success">Save changes</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@stop
