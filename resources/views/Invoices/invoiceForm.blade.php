@extends ('Common.template')

@section('title', ' Invoices :: Create')

@section('page.title')
	@if ($invoice->id)
		Edit Invoice
	@else
		Create New Invoice
	@endif
@stop

@section('breadcrumbs')
	@if ($invoice->id)
		<li class="active">Edit Invoice</li>
	@else
		<li class="active">Create New Invoice</li>
	@endif
@stop

@section('content')
	<div class="invoice p-3 mb-3">
		@if (count($errors) > 0)
			<div class="alert alert-error">
				<ul>
					@foreach ($errors->all() as $error)
						<li>{{$error}}</li>
					@endforeach
				</ul>
			</div>
		@endif
		<form method="post" action="/admin/invoices/{{$invoice->id}}">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">
			@if ($invoice->id)
				<input type="hidden" name="_method" value="PUT">
			@endif

			<div class="row">
        <div class="col-12">
					<h4>
						<i class="fa fa-globe"></i> {{$invoice->user->siteSettings('name')}}
						<select name="estimate">
							<option value="N" {{ old('estimate', $invoice->estimate) ? '' : 'selected' }}>Invoice</option>
							<option value="Y" {{ !old('estimate', $invoice->estimate) ? '' : 'selected' }}>Estimate</option>
						</select>
						<small class="float-right">Date: {{date('d/m/Y')}}</small>
					</h4>
				</div>
			</div>
			<div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
					From
					<address>
						<strong>{{ $invoice->user->mailingContact->address->business_name }}</strong><br>
						<div>{{ $invoice->user->mailingContact->address->address_1 }}</div>
						<div>{{ $invoice->user->mailingContact->address->address_2 }}</div>
						<div>{{ $invoice->user->mailingContact->address->address_3 }}</div>
						<div>{{ $invoice->user->mailingContact->address->address_4 }}</div>
						<div>
							{{ $invoice->user->mailingContact->address->city }},
							@if($invoice->user->mailingContact->address->county) {{ $invoice->user->mailingContact->address->county->name }} @endif&nbsp;
							{{ $invoice->user->mailingContact->address->postal_code }}</div>
						<div>
							@if ($invoice->user->mailingContact->address->country)
								{{ $invoice->user->mailingContact->address->country->name }}
							@endif
						</div>
						<div>{{ $invoice->user->mailingContact->address->phone }}</div>
						<div>{{ $invoice->user->mailingContact->address->fax }}</div>
						<div>{{ $invoice->user->mailingContact->address->email }}</div>
					</address>
				</div>
				<div class="col-sm-4 invoice-col">
					<div id="address">
						To
					@if ($invoice->customer_id && $invoice->address)
						<input type="hidden" name="customer[id]" value="{{ $invoice->customer_id }}">
						<address>
							<strong>{{ $invoice->address->business_name }}</strong><br>
							<div>{{ $invoice->address->address_1 }}</div>
							<div>{{ $invoice->address->address_2 }}</div>
							<div>{{ $invoice->address->address_3 }}</div>
							<div>{{ $invoice->address->address_4 }}</div>
							<div>
								{{ $invoice->address->city }}, @if ($invoice->address->county) {{ $invoice->address->county->name }} @endif {{ $invoice->address->postal_code }}
							</div>
							<div>@if ($invoice->address->county) {{ $invoice->address->country->name }} @endif</div>
							<div>{{ $invoice->address->phone }}</div>
							<div>{{ $invoice->address->fax }}</div>
							<div>{{ $invoice->address->email }}</div>
							<input type="hidden" name="customer[address][id]" value="{{$invoice->address->id}}">
							<input type="hidden" name="customer[address_1]" value="{{$invoice->address->address_1}}">
							<input type="hidden" name="customer[address_2]" value="{{$invoice->address->address_2}}">
							<input type="hidden" name="customer[address_3]" value="{{$invoice->address->address_3}}">
							<input type="hidden" name="customer[address_4]" value="{{$invoice->address->address_4}}">
							<input type="hidden" name="customer[city]" value="{{$invoice->address->city}}">
							<input type="hidden" name="customer[county]" value="@if ($invoice->address->county) {{$invoice->address->county->id}} @endif">
							<input type="hidden" name="customer[country]" value="{{$invoice->address->country_id}}">
							<input type="hidden" name="customer[postal_code]" value="{{$invoice->address->postal_code}}">
							<input type="hidden" id="county_id" value="{{$invoice->address->county_id}}"/>
						</address>
					@else
						<input type="hidden" name="customer[id]" value="new">
						<input type="hidden" name="customer[address][id]" value="new">
						<address>
							<div>
								<span style="border-style: solid; border-radius: 5px; border-width: 1px;"><input type="text" name="customer[name]" value="{{old('customer.name')}}" required placeholder="Name"></span>
								<span style="border-style: solid; border-radius: 5px; border-width: 1px;"><input type="email" required name="customer[email]" value="{{old('customer.email')}}" placeholder="Email Address"></span>
							</div>
							<span style="border-style: solid; border-radius: 5px; border-width: 1px;"><input type="text" name="customer[address_1]" value="{{old('customer.address_1')}}" placeholder="Address Line 1"></span>
							<span style="border-style: solid; border-radius: 5px; border-width: 1px;"><input type="text" name="customer[address_2]" value="{{old('customer.address_2')}}" placeholder="Address Line 2"></span>
							<div>
								<span style="border-style: solid; border-radius: 5px; border-width: 1px;"><input type="text" name="customer[city]" value="{{old('customer.city')}}" placeholder="City"></span>
								<span style="border-style: solid; border-radius: 5px; border-width: 1px;"><input type="text" name="customer[postal_code]" value="{{old('customer.postal_code')}}" required placeholder="Postal/Zip Code"></span>
							</div>
							<div>
								<span style="border-style: solid; border-radius: 5px; border-width: 1px;"><select id="country_id" name="customer[country]">
									<option disabled selected hidden>Select Country</option>
									@foreach ($countries as $country)
										@if(old('customer.country'))
											<option value="{{$country->id}}" selected>{{$country->name}}</option>
										@else
											<option value="{{$country->id}}">{{$country->name}}</option>
										@endif
									@endforeach
								</select></span>
							</div>
							<div>
								<span style="border-style: solid; border-radius: 5px; border-width: 1px;">
									<select id="county_id" name="customer[county]">
									@if(old('customer.county'))
										<option value="{{ old('customer.county') }}" selected>{{$funcCountyName(old('customer.county'))}}</option>
									@else
										<option value="3416" selected hidden>Select Region</option>
									@endif
								</select></span>
							</div>
						</address>
						<ul id="customerAutoComplete"></ul>
					@endif
					</div>
				</div>
				<div class="col-sm-4 invoice-col">
					<b>Invoice: </b>{{ Settings::get('invoice.prefix') }}<span style="border-style: solid; border-radius: 5px; border-width: 1px;"><input type="text" name="invoiceNumber" value="{{ old('invoiceNumber', $invoice->invoice_number) }}" required></span><br>
					<b>Payment Due:</b> <span style="border-style: solid; border-radius: 5px; border-width: 1px;"><input type="date" name="duedate" value="{{ old('duedate', date('Y-m-d', strtotime($invoice->due_at))) }}" id="duedate" required></span>
					@if($invoice->id)
						<b>Status: </b>
						<select name="status">
							@if($invoice->status == 0)
							<option value="0" selected>Unpaid</option>
							@else
							<option value="0">Unpaid</option>
							@endif
							@if($invoice->status == 1)
							<option value="1" selected>Paid</option>
							@else
							<option value="1">Paid</option>
							@endif
							@if($invoice->status == 2)
							<option value="2" selected>Overdue</option>
							@else
							<option value="2">Overdue</option>
							@endif
							@if($invoice->status == 3)
							<option value="3" selected>Refunded</option>
							@else
							<option value="3">Refunded</option>
							@endif
							@if($invoice->status == 4)
							<option value="4" selected>Cancelled</option>
							@else
							<option value="4">Cancelled</option>
							@endif
							@if($invoice->status == 5)
							<option value="5" selected>Pending</option>
							@else
							<option value="5">Pending</option>
							@endif
						</select>
					@endif
				</div>
			</div>

			<div class="row">
				<div class="col-xs-12 table-responsive">
					<table class="table table-striped" id="items">
						<thead>
							<tr>
								<th>Item</th>
								<th class="col-xs-3">Description</th>
								<th class="col-xs-2">Tax Class</th>
								<th></th>
								<th class="col-xs-1">Price</th>
								<th class="col-xs-1">Qty</th>
								<th></th>
								<th class="col-xs-1">Subtotal</th>
								<th></th>
								<th class="col-xs-1">Tax</th>
								<th class="buttons"></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>

			<div class="row">
				<div class="col-6">
					<p class="lead">Additional Comments:</p>

					<p class="text-muted well well-sm no-shadow" style="margin-top: 10px; border-style: solid; border-radius: 10px; border-width: 2px;">
						<textarea name="comments" placeholder="">{{ old('comments', $invoice->comments) }}</textarea>
					</p>
				</div>

				<div class="col-6">
					<div class="table-responsive">
						<table class="table" id="totals">
							<tr>
								<th></th>
								<th style="width:50%">Subtotal:</th>
								<td class="text-right">{!! $currency !!}</td>
								<td id="subtotal">0.00</td>
								<td class="buttons"></td>
							</tr>
							<tr>
								<th></th>
								<th>Tax:</th>
								<td class="text-right">{!! $currency !!}</td>
								<td>
									<input type="hidden" name="subtotal[tax][item]" value="Tax">
									<input type="text" readonly id="taxtotal" name="subtotal[tax][price]" value="0.00">
									<input type="hidden" id="taxtotal2" value="0.00">
								</td>
								<td></td>
							</tr>
							<tr>
								<th></th>
								<th>Total:</th>
								<td class="text-right">{!! $currency !!}</td>
								<td id="total">0.00</td>
								<td></td>
							</tr>
						</table>
					</div>
				</div>
			</div>

			<div class="row no-print">
				<div class="col-xs-12">
					<button id="btnCreateInvoice" type="submit" class="btn btn-success float-right">
						<i class="fa fa-credit-card"></i> Send Invoice
					</button>
					<!--<button id="btnCalcTax" type="button" class="btn btn-success float-right">
						<i class="fa fa-check"></i> Calculate Tax
					</button>-->
				</div>
			</div>
		</form>
	</div>
@stop

@section('javascript')
<script id="invoice-row-template" type="text/x-handlebars-template">
	<tr>
		<td class="item"><input type="text" name="record[@{{i}}][item]" value="@{{item}}"></td>
		<td class="description"><input type="text" name="record[@{{i}}][description]" value="@{{description}}"></td>
		<td class="taxclass">
			<select name="record[@{{i}}][taxclass]">
			@foreach($taxClasses as $class)
				@if($class->default)
					<option value="{{$class->id}}">{{$class->name}}</option>
				@endif
			@endforeach
				<option value="0">Nontaxable</option>
			@foreach($taxClasses as $class)
				@if(!$class->default)
					<option value="{{$class->id}}">{{$class->name}}</option>
				@endif
			@endforeach
			</select>
		</td>
		<td class="text-right">{!! $currency !!}</td>
		<td class="price"><input type="number" min="0.00" step="0.01" name="record[@{{i}}][price]" value="@{{price}}" placeholder="0.00"></td>
		<td class="qty"><input type="number" min="1" step="any" name="record[@{{i}}][quantity]" value="@{{quantity}}" placeholder="1"></td>
		<td class="text-right">{!! $currency !!}</td>
		<td class="subtotal">0.00</td>
		<td class="text-right">{!! $currency !!}</td>
		<td class="tax"><input readonly type="text" name="record[@{{i}}][tax]" value="@{{tax}}" placeholder="0.00"></td>
		<td class="buttons">
			<button type="button" class="add btn btn-success float-right btn-circle"><i class="fa fa-plus"></i></button>
			@{{#if minus}}
				<button type="button" class="del btn btn-danger float-right btn-circle"><i class="fa fa-minus"></i></button>
			@{{/if}}
		</td>
	</tr>
</script>

<script id="total-row-template" type="text/x-handlebars-template">
	<tr>
		<td class="taxclass">
			<select name="subtotal[@{{i}}][taxclass]">
			@foreach($taxClasses as $class)
				@if($class->default)
					<option value="{{$class->id}}">{{$class->name}}</option>
				@endif
			@endforeach
				<option value="0">Nontaxable</option>
			@foreach($taxClasses as $class)
				@if(!$class->default)
					<option value="{{$class->id}}">{{$class->name}}</option>
				@endif
			@endforeach
			</select>
			<input type="hidden" class="tax" value="0"/>
		</td>
		<td>
			<input type="text" name="subtotal[@{{i}}][item]" placeholder="New Total Item" value="@{{item}}">
		</td>
		<td class="text-right">{!! $currency !!}</td>
		<td>
			<input class="value" type="number" min="0.00" step="0.01" name="subtotal[@{{i}}][price]" placeholder="0.00" value="@{{price}}">
		</td>
		<td class="buttons">
			<button type="button" class="add btn btn-success float-right btn-circle"><i class="fa fa-plus"></i></button>
			@{{#if minus}}
				<button type="button" class="del btn btn-danger float-right btn-circle"><i class="fa fa-minus"></i></button>
			@{{/if}}
		</td>
	</tr>
</script>

<script id="address-form-template" type="text/x-handlebars-template">
	<address>
		<div>
			<input type="text" name="customer[name]" value="{{old('customer.name')}}" required placeholder="Name">
			<input type="email" required name="customer[email]" value="{{old('customer.email')}}" placeholder="Email Address">
		</div>
		<div><input type="text" name="customer[address_1]" value="{{old('customer.address_1')}}" placeholder="Address Line 1"></div>
		<div><input type="text" name="customer[address_2]" value="{{old('customer.address_2')}}" placeholder="Address Line 2"></div>
		<div>
			<input type="text" name="customer[city]" value="{{old('customer.city')}}" placeholder="City">
			<input type="text" name="customer[postal_code]" value="{{old('customer.postal_code')}}" required placeholder="Postal/Zip Code">
		</div>
		<div>
			<select id="country_id" name="customer[country]">
				<option disabled selected hidden>Select Country</option>
				@foreach ($countries as $country)
					<option value="{{$country->id}}">{{$country->name}}</option>
				@endforeach
			</select>
		</div>
		<div>
			<select id="county_id" name="customer[county]">
				<option disabled selected hidden>Select Region</option>
			</select>
		</div>
	</address>
</script>

<script id="address-template" type="text/x-handlebars-template">
	<address>
		@{{ address.contact_name }} @{{#if address.email}}(@{{address.email}})@{{/if}}<br>
		@{{#if address.address_1 }} @{{ address.address_1 }}<br> @{{/if}}
		@{{#if address.address_2 }} @{{ address.address_2 }}<br> @{{/if}}
		@{{#if address.address_3 }} @{{ address.address_3 }}<br> @{{/if}}
		@{{#if address.address_4 }} @{{ address.address_4 }}<br> @{{/if}}
		@{{#if address.city }} @{{ address.city}}, @{{/if}} @{{ address.postal_code}}<br>
		@{{address.county.name}}
		<input type="hidden" id="county_id2" value="@{{address.county.id}}"/>
	</address>
</script>

<script>
	var currency = '{!! $currency !!}';
	var countyIdToSelectOnLoad = '';
	var customers = JSON.parse('{!! $jsonCustomers !!}');
	console.log(customers);
	(function($) {
		var i = {
			'record': 0,
			'subtotal': 0
		};

		$('[name="customer[name]"]').on('keyup', function() {
			var query = $(this).val();
			var temphtml = '<li data-target="new">' + query + ' (new)</li>';
			$.each(customers,function(k,v){
				if(v.name.toLowerCase().indexOf(query.toLowerCase()) > -1)
				{
					temphtml = temphtml + '<li data-target="' + k + '">' + v.name + '<br>(' + v.email + ')</li>';
				}
			});
			$('#customerAutoComplete').html(temphtml).show();
		});

		$('body').on('click','#customerAutoComplete > li',function(){
			$('#customerAutoComplete').hide();
			if($(this).data('target') !== 'new')
			{
				$('[name="customer[id]"]').val(customers[$(this).data('target')].id);
				$('[name="customer[address][id]"]').val(customers[$(this).data('target')].address_id);

				$('[name="customer[name]"]').val(customers[$(this).data('target')].name);
				$('[name="customer[email]"]').val(customers[$(this).data('target')].email);
				$('[name="customer[address_1]"]').val(customers[$(this).data('target')].address_1);
				$('[name="customer[address_2]"]').val(customers[$(this).data('target')].address_2);
				$('[name="customer[city]"]').val(customers[$(this).data('target')].city);
				$('[name="customer[postal_code]"]').val(customers[$(this).data('target')].postal_code);
				countyIdToSelectOnLoad = customers[$(this).data('target')].county_id;
				$('[name="customer[country]"]').val(customers[$(this).data('target')].country_id);
				getRegions();
			}
			else
			{
				$('[name="customer[id]"]').val('new');
				$('[name="customer[address][id]"]').val('new');
				$('[name="customer[name]"]').val();
				$('[name="customer[email]"]').val();
				$('[name="customer[address_1]"]').val();
				$('[name="customer[address_2]"]').val();
				$('[name="customer[city]"]').val();
				$('[name="customer[postal_code]"]').val();
			}
		});

		$('body').on('change', '[name$="[price]"], [name$="[quantity]"], [name$="[tax]"]', function() {
			var $row = $(this).parents('tr');
			var price = $row.find('[name$="[price]"]').val();
			var qty = $row.find('[name$="[quantity]"]').val();

			if (price.length) {
				var subtotal = price * qty;
				$row.find('.subtotal').text(subtotal.toFixed(2));
			}

			updateTotals();
		});

		$('body').on('click', '#items .add', function() {
			$('#items tbody').append(Handlebars.compile($('#invoice-row-template').html())({
				'minus': true,
				'i': i.record++
			}));
		});

		$('body').on('click', '#items .del', function() {
			$(this).parents('tr').remove();
			updateTotals();
		});

		$('body').on('click', '#totals .add', function() {
			$(this).parents('tr').after(Handlebars.compile($('#total-row-template').html())({
				'minus': true,
				'i': i.subtotal++
			}))
		});

		$('body').on('click', '#totals .del', function() {
			$(this).parents('tr').remove();
		});

		$('body').on('change', '#taxtotal', function() {
			updateTaxTotalsOnly();
		});

		function getRegions()
		{
			$.ajax({
				url: '/helper/counties/'+$('#country_id').val(),
				type: 'GET',
				dataType: 'json'
			})
			.done(function(data) {
				$('#county_id').empty().show();
				$.each(data,function(k,v)
				{
					var checked = '';
					if(countyIdToSelectOnLoad == v.id)
					{
						checked = ' selected'
						countyIdToSelectOnLoad = '';
					}
					$('#county_id').append('<option value="'+v.id+'"' + checked + '>'+v.name+'</option>');
				});
			})
			.fail(function() {
				$('#address').text('Connection error.');
			});
		}


		$('body').on('change', '#country_id', getRegions);

		$('body').on('mouseup change', '#items .price input, #items .qty input, #totals .value, #country_id, #county_id', function() {
			//$('#btnCalcTax').find('i').addClass('fa-spin fa-refresh').removeClass('btn-danger');
			var arrClass = [];
			var cId = 0;
			if($('#county_id').length > 0 && $('#county_id').val() > 0)
			{
				cId = $('#county_id').val();
			}
			else if($('#county_id2').length > 0 && $('#county_id2').val() > 0)
			{
				cId = $('#county_id2').val();
			}
			$.ajax({
				headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
				url: '/admin/invoices/taxRates',
				type: 'POST',
				data: 'countyId='+cId
			})
			.done(function(data, textStatus, jqXHR) {
				data = JSON.parse(data);
				//$('#btnCalcTax').find('i').removeClass('fa-spin fa-refresh').addClass('btn-success');
				var subTotals = $('#items tbody .subtotal');
				var taxableTotals = $('#totals .taxclass');
				var totalTaxtotal = 0;
				//$.each(JSON.parse(data),function(k,v){
				//	arrClass[v.class_id] = v.rate;
				//})
				subTotals.each(function(index, el) {
					var ClassId = Number($(el).parent().find('.taxclass select').val());
					if(ClassId != 0)
					{
						var rate = data[ClassId].rate;
						var tempTax = Number($(el).text()) * (rate / 100);
					}
					else
					{
						var tempTax = 0;
					}
					$(el).parent().find('.tax input').val(tempTax.toFixed(2));
				});
				taxableTotals.each(function(index, el) {
					var ClassId = Number($(el).find('select').val());
					var totalTaxTotal = $(el).parent().find('input.value').val();
					if(ClassId != 0)
					{
						var rate = data[ClassId].rate;
						var tempTax = Number(totalTaxTotal) * (rate / 100);
					}
					else
					{
						var tempTax = 0;
					}

					$(el).parent().find('.tax').val(tempTax.toFixed(2));
				});
				updateTotals();
			})
			.fail(function(data) {
				//$('#btnCalcTax').find('i').removeClass('fa-spin fa-refresh btn-success').addClass('btn-danger');
			});
		});

		function updateTotals() {
			var $subTotals = $('#items tbody .subtotal');
			var $taxTotals = $('#items tbody .tax input');
			var $taxableTotals = $('#totals .taxclass .tax');
			var totalSubtotal = 0;
			var itemTaxtotal = 0;
			var taxableTotalsTaxtotal = 0;
			var totalTaxtotal = 0;

			$subTotals.each(function(index, el) {
				totalSubtotal = totalSubtotal + Number($(el).text());
			});

			$taxTotals.each(function(index, el) {
				itemTaxtotal = itemTaxtotal + Number($(el).val());
			});

			$taxableTotals.each(function(index, el) {
				taxableTotalsTaxtotal = taxableTotalsTaxtotal + Number($(el).val());
			});

			//if user is changing item tax its best to assume they arent working on totals atm and we should base tax on item taxes only?
			totalTaxtotal = itemTaxtotal + taxableTotalsTaxtotal;

			$('#subtotal').text(totalSubtotal.toFixed(2));
			$('#taxtotal').val(totalTaxtotal.toFixed(2));

			var $totals = $('#totals [name$="[price]"]');
			var total = Number(totalSubtotal);

			$totals.each(function(index, el) {
				total = total + Number($(el).val());
			});

			$('#total').text(total.toFixed(2));
		}
		function updateTaxTotalsOnly() {
			var $taxTotals = $('#items tbody .tax input');
			var total = Number($('#total').val());
			var totalTaxtotal = 0;

			totalTaxtotal = Number($('#taxtotal').val());

			$('#taxtotal').val(totalTaxtotal.toFixed(2));

			total = total + totalTaxtotal;

			$('#total').text(total.toFixed(2));
		}

		/* jshint ignore:start */
		@if (count(old('record', $invoice->items)))
			var records = {!! json_encode(old('record', $invoice->items)) !!};

			$(records).each(function(k, el) {
				el.i = i.record++;
				el.minus = el.i !== 0;

				$('#items tbody').append(
					Handlebars.compile($('#invoice-row-template').html())(el)
				).find('[name$="[price]"]').trigger('change');
			});

			updateTotals();
		@else
			$('#items tbody').html(Handlebars.compile($('#invoice-row-template').html())({
				'i': i.record++,
				'minus': false
			}));
		@endif

		@if (count(old('subtotal', $invoice->totals)))
			var subtotals = {!! json_encode(old('subtotal', $invoice->totals)) !!}

			$(subtotals).each(function(k, el) {
				el.i = i.subtotal++;
				el.minus = el.i !== 0;

				$('#totals tbody tr:nth-last-child(2)').before(Handlebars.compile($('#total-row-template').html())(el));
			});

			updateTotals();
		@else
			$('#totals tbody tr:nth-last-child(2)').before(Handlebars.compile($('#total-row-template').html())({
				'i': i.subtotal++,
				'minus': false
			}));
		@endif
		/* jshint ignore:end */
	})(jQuery);
</script>
@stop

@section('css')
<style>
	#customerAutoComplete{
		display:none;
		list-style-type:none;
		position:absolute;
		top:0;
		left:25%;
		text-align:center;
		width:auto;
		z-index:100;
		height:auto;
		overflow:hidden;
		padding:5px;
		background-color:#fff;
		border:1px solid #333;
		border-radius:3px;
	}
	#customerAutoComplete li{
		display:block;
		cursor:pointer;
		margin-bottom:5px;
	}
	#customerAutoComplete li:hover{
		font-weight:bold;
	}
	#totals input, #items input, textarea
	{
		width:100%;
	}
	#totals input.totalVal,#totals input.totalTitle{
		width:auto;
	}
	input, select, textarea
	{
		border-color:transparent;
		background-color:transparent;
	}
	.btn-circle {
		width: 30px;
		height: 30px;
		text-align: center;
		padding: 6px 0;
		font-size: 12px;
		line-height: 1.428571429;
		border-radius: 15px;
		margin-left:6px;
	}
	.buttons{
		width:88px;
	}
	#btnCalcTax{
		margin-right:10px;
	}
</style>
@stop
