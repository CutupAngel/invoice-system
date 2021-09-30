@extends ('Common.template')

@section('title', 'Invoice Settings')

@section('page.title', 'Invoice Settings')

@section('breadcrumbs')
	<li class="active">{{ trans('backend.inv-welcome') }}</li>
@stop

@section('content')
	@if(session()->has('success'))
		<div class="alert alert-success">
			{{ session()->get('success') }}
		</div>
	@endif

	@if (count($errors) > 0)
		<div class="alert alert-dismissible alert-danger">
			<button type="button" class="close" data-dismiss="alert">×</button>
			@foreach ($errors->all() as $error)
				{{$error}}<br>
			@endforeach
		</div>
	@endif

	<form method="post">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">
		<div class="row">
			<div class="col-lg-6">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">{{ trans('backend.inv-default') }}</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="orderReturnURL">{{ trans('backend.inv-returnurl') }}</label>
							<input type="text" class="form-control" name="orderReturnURL" value="{{ old('orderReturnURL', Settings::get('invoice.orderReturnURL')) }}">
						</div>
						<div class="form-group">
							<label for="prefix">{{ trans('backend.inv-invoicepre') }}</label>
							<input type="text" class="form-control" name="prefix" value="{{ old('prefix', Settings::get('invoice.prefix')) }}">
						</div>
						<div class="form-group">
							<label for="startNumber">{{ trans('backend.inv-startnum') }}</label>
							<input type="number" class="form-control" name="startNumber" value="{{ old('startNumber', Settings::get('invoice.startNumber')) }}" placeholder="0">
						</div>
						<div class="form-group">
							<label for="paymentsDue">{{ trans('backend.inv-paymentsdue') }}</label>
							<select name="paymentsDue" autocomplete="off" class="form-control">
								@foreach ($paymentOptions as $value => $option)
									<option value="{{ $value }}" {!! old('paymentsDue', Settings::get('invoice.paymentsDue')) == $value ? ' selected="selected"' : '' !!}>
										{{ $option }}
									</option>
								@endforeach
							</select>
						</div>
						<div class="form-group">
							<label for="lateFees">{{ trans('backend.inv-latefees') }}</label>
							<div class="input-group">
								<span class="input-group-text">%</span>
								<input type="numeric" class="form-control" name="lateFees" value="{{ old('lateFees', Settings::get('invoice.lateFees')) }}">
								<select name="lateFeesTax" class="form-control">
								@foreach($taxclasses as $taxclass)
									@if($taxclass->default)
										@if(old('lateFeesTax', Settings::get('invoice.lateFeesTax')) == $taxclass->id)
										<option value="{{$taxclass->id}}" selected="selected">{{$taxclass->name}}</option>
										@else
										<option value="{{$taxclass->id}}">{{$taxclass->name}}</option>
										@endif
									@endif
								@endforeach
								@if(old('lateFeesTax', Settings::get('invoice.lateFeesTax')) === '0')
								<option value="0" selected="selected">Nontaxable</option>
								@else
								<option value="0">Nontaxable</option>
								@endif
								@foreach($taxclasses as $taxclass)
									@if(!$taxclass->default)
										@if(old('lateFeesTax', Settings::get('invoice.lateFeesTax')) == $taxclass->id)
										<option value="{{$taxclass->id}}" selected="selected">{{$taxclass->name}}</option>
										@else
										<option value="{{$taxclass->id}}">{{$taxclass->name}}</option>
										@endif
									@endif
								@endforeach
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="reminders">{{ trans('backend.inv-auto') }}</label>
							<div class="input-group">
								<span class="input-group-text">{{ trans('backend.inv-autorem') }}</span>
								<input type="number" class="form-control" name="reminders" min="1" value="{{ old('reminders', Settings::get('invoice.reminders')) }}" step="0">
								<span class="input-group-text">{{ trans('backend.inv-autoremday') }}</span>
							</div>
						</div>
						<div class="form-group">
							<label for="reminders4suspend">{{ trans('backend.inv-reminders') }}</label>
							<div class="input-group">
								<input type="number" class="form-control" name="reminders4suspend" min="1" value="{{ old('reminders4suspend', Settings::get('invoice.reminders4suspend')) }}" step="1">
								<span class="input-group-text">{{ trans('backend.inv-reminderbox') }}</span>
							</div>
						</div>
						<div class="form-group">
							<label for="days2send">{{ trans('backend.inv-send') }}</label>
							<div class="input-group">
								<span class="input-group-text">{{ trans('backend.inv-send') }}</span>
								<input type="number" class="form-control" name="days2send" min="1" value="{{ old('days2send', Settings::get('invoice.days2send')) }}" step="1">
								<span class="input-group-text">{{ trans('backend.inv-senddays') }}</span>
							</div>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">{{ trans('backend.inv-totalingorder') }}</h3>
					</div>
					<div class="card-body">
						{{ trans('backend.inv-totalingorder-desc') }}
						<div class="form-group totalingSubtotal">
							<div class="input-group">
								<span class="input-group-text">1</span>
								<div class="form-control">{{ trans('backend.inv-totalingorder-subtotal') }}</div>
								<span class="input-group-text"><i class="fa fa-times-circle"></i></span>
								<span class="input-group-text"><i class="fa fa-times-circle"></i></span>
							</div>
						</div>
						@foreach(Settings::get('invoice.total-order',['fixeddiscount','discountcode','customtotals','shipping','tax']) as $k=>$v)
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-text position">{{$k + 2}}</span>
								<div class="form-control">{{ trans('backend.inv-totalingorder-' . $v) }}</div>
								<span class="input-group-text totalingOrderUp"><i class="fa fa-arrow-circle-up"></i></span>
								<span class="input-group-text totalingOrderDown"><i class="fa fa-arrow-circle-down"></i></span>
								<input type="hidden" value="{{$v}}" name="total-order[{{$k}}]"/>
							</div>
						</div>
						@endforeach
						<div class="form-group totalingGrandtotal">
							<div class="input-group">
								<span class="input-group-text">{{$k + 3}}</span>
								<div class="form-control">{{ trans('backend.inv-totalingorder-grandtotal') }}</div>
								<span class="input-group-text"><i class="fa fa-times-circle"></i></span>
								<span class="input-group-text"><i class="fa fa-times-circle"></i></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">{{ trans('backend.inv-currencypanel') }}</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="defaultCurrency">{{ trans('backend.inv-defcurrency') }}</label>
							<select name="defaultCurrency" autocomplete="off" class="form-control">
								@foreach ($currencies as $currency)
									<option value="{{ $currency->id }}" {!! ($currency->id == Settings::get('site.defaultCurrency',4)) ? ' selected="selected"' : '' !!}>
										{{ $currency->name }}
									</option>
								@endforeach
							</select>
						</div>
						<div class="form-group">
							<label for="currency">{{ trans('backend.inv-currencies') }}</label>
							<select name="currency[]" autocomplete="off" multiple class="form-control">
								<option disabled {{ old('currency', Settings::get('invoice.currency')) == '' ? 'selected' : '' }} hidden></option>
								@foreach ($currencies as $currency)
									<option value="{{ $currency->id }}" {{ in_array($currency->id, old('currency', Settings::get('invoice.currency', [3,4,5]))) ? 'selected' : '' }}>
										{{ $currency->name }}
									</option>
								@endforeach
							</select>
						</div>
						<div class="form-group">
							<label for="exchangerate">{{ trans('backend.inv-exchangeratefee') }}</label>
							<input type="number" class="form-control" name="exchangerate" value="{{ old('invoice.exchangerate', Settings::get('invoice.exchangerate','0.00')) }}" step="0.01">
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">{{ trans('backend.inv-vatinfo') }}</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="vat.name">{{ trans('backend.inv-vatname') }}</label>
							<input type="text" class="form-control" name="vat[name]" value="{{ old('vat.name', Settings::get('invoice.vat.name')) }}" step="1">
						</div>
						<div class="form-group">
							<label for="vat.number">{{ trans('backend.inv-vatno') }}</label>
							<input type="text" class="form-control" name="vat[number]" value="{{ old('vat.number', Settings::get('invoice.vat.number')) }}" step="1">
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">{{ trans('backend.inv-companyset') }}</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="companyRegistration">{{ trans('backend.inv-companyreg') }}</label>
							<input type="text" class="form-control" name="companyRegistration" value="{{ old('companyRegistration', Settings::get('invoice.companyRegistration')) }}" step="1">
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">{{ trans('backend.inv-discountset') }}</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<div class="checkbox">
							  <label><input name="fixedDiscount" type="checkbox" value="1" @php if(Settings::get('invoice.fixedDiscount')) echo 'checked'; @endphp> Enable Fixed Discount</label>
							</div>
						</div>
						<div class="form-group">
							<div class="checkbox">
							  <label><input name="discountCode" type="checkbox" value="1" @php if(Settings::get('invoice.discountCode')) echo 'checked'; @endphp> Enable Discount Code</label>
							</div>
						</div>
					</div>
				</div>
				<!--
				<div class="panel panel-default">
					<div class="panel-heading">
						{{ trans('backend.inv-accepted') }}
					</div>
					<div class="panel-body">
						@foreach ($paymentTypes as $id => $title)
							<div class="form-group checkbox">
								<label for="{{ $id }}">
									<input
										type="checkbox"
										name="paymentType[{{ $id }}]"
										value="1"
										{{ old("paymentType.{$id}", Settings::get("invoice.paymentType.{$id}")) == '1' ? 'checked' : ''}}
									>
									{{ $title }}
								</label>
							</div>
						@endforeach
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">
						{{ trans('backend.inv-creditcards') }}
					</div>
					<div class="panel-body">
						@foreach ($creditCards as $id => $card)
							<div class="form-group checkbox">
								<label>
									<input
										type="checkbox"
										name="creditcards[{{ $id }}]"
										value="1"
										{{ old("creditcards.{$id}", Settings::get("invoice.creditcards.{$id}")) == '1' ? 'checked' : ''}}
									>
									{{ $card }}
								</label>
							</div>
						@endforeach
					</div>
				</div>-->
				<button type="submit" class="btn btn-success float-right">{{ trans('backend.inv-save') }}</button>
			</div>
		</div>
	</form>
@stop

@section('javascript')
<script type="text/javascript">

	$("input[name='reminders']").blur(function(){
		if($("input[name='reminders']").val() < 1)
		{
				$("input[name='reminders']").val(1);
		}
	});

	$('body').on('click','.totalingOrderUp',function(){
		if(!$(this).parent().parent().prev().hasClass('totalingSubtotal'))
		{
			var currentPosition = parseInt($(this).parent().parent().find('.position').html());
			var newPosition = currentPosition - 1;
			var movedElement = $(this).parent().parent().insertBefore($(this).parent().parent().prev());
			movedElement.find('input[type=hidden]').attr('name','total-order[' + parseInt(newPosition - 2) + ']');
			movedElement.find('.position').html(newPosition);
			movedElement.next().find('.position').html(currentPosition);
			movedElement.prev().find('input[type=hidden]').attr('name','total-order[' + parseInt(currentPosition - 2) + ']');
		}
	});
	$('body').on('click','.totalingOrderDown',function(){
		if(!$(this).parent().parent().next().hasClass('totalingGrandtotal'))
		{
			var currentPosition = parseInt($(this).parent().parent().find('.position').html());
			var newPosition = currentPosition + 1;
			var movedElement = $(this).parent().parent().insertAfter($(this).parent().parent().next());
			movedElement.find('input[type=hidden]').attr('name','total-order[' + parseInt(newPosition - 2) + ']');
			movedElement.find('.position').html(newPosition);
			movedElement.prev().find('.position').html(currentPosition);
			movedElement.prev().find('input[type=hidden]').attr('name','total-order[' + parseInt(currentPosition - 2) + ']');
		}
	});
</script>
@stop
