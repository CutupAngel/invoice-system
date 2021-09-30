@extends('Common.template')

@section('title', ' Plans')
@section('page.title', 'Plans')
@section('page.subtitle')
	{{$type}} Plan
@stop

@section('breadcrumbs')
	<li><a href="/plans">View Plans</a></li>
	<li class="active">{{$type}} Plan</li>
@stop

@section ('content')
	<form method="post" enctype="multipart/form-data">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">

		<div class="row">
			<div class="col-sm-6">
				<div class="box box-primary">
					<div class="box-header">
						<h3 class="box-title">Plan Info</h3>
					</div>

					<div class="box-body">
						@if (count($errors) > 0)
							<div class="alert alert-danger">
								@foreach ($errors->all() as $error)
									{{$error}}<br>
								@endforeach
							</div>
						@endif

						<div class="form-group">
							<label for="name">Name: </label>
							<input type="text" name="name" id="name" class="form-control" value="{{old('name', $plan->name)}}" required>
						</div>
						<div class="form-group">
							<label for="description">Description: </label>
							<textarea name="description" id="description" class="form-control">{{old('description', $plan->description)}}</textarea>
						</div>
						<div class="form-group">
							<label for="name"># Clients: </label>
							<input type="number" name="clients" id="clients" class="form-control" value="{{old('clients', $plan->clients)}}" required>
						</div>
						<div class="form-group">
							<label for="name"># Invoices: </label>
							<input type="number" name="invoices" id="invoices" class="form-control" value="{{old('invoices', $plan->invoices)}}" required>
						</div>
						<div class="form-group">
							<label for="name"># Staff Accounts: </label>
							<input type="number" name="staff" id="staff" class="form-control" value="{{old('staff', $plan->staff)}}" required>
						</div>
						<div class="form-group">
							<label for="vat">Apply VAT: </label>
							<select id="vat" name="vat" class="form-control">
								<option value="0" <?= (old('vat', $plan->tax) == 1) ? '': 'selected'; ?>>No</option>
								<option value="1" <?= (old('vat', $plan->tax) == 0) ? '': 'selected'; ?>>Yes</option>
							</select>
						</div>
						<div class="form-group">
							<label for="trial">Free Trial: </label>
							<div class="input-group">
								<input type="number" name="trial" id="trial" min="0" class="form-control" placeholder="0" value="{{old('trial', $plan->trial)}}">
								<div class="input-group-addon">
									Days
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="box box-primary">
					<div class="box-header">
						<h3 class="box-title">Cycles</h3>
					</div>

					<div class="box-body" id="cycles">
						{{-- this is handled by javascript below --}}
					</div>

					<div class="box-footer">
						<div class="form-group">
							<a href="#" class="addCycle"> <i class="fa fa-plus"></i> Add Another Cycle</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<a href="/orders"><button type="button" class="btn btn-default"><i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
		<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"></i> Save Package</button>
	</form>
@stop

@section ('css')
	<link rel="stylesheet" href="https://v2.b-cdn.uk/plugins/iCheck/all.css">
@stop

@section ('javascript')
	<script id="cycle-template" type="text/x-handlebars-template">
		<div class="cycle">
			<div class="float-right removeTools">
				<a href="#" class="removeCycle"><i class="fa fa-trash-o"></i></a>
			</div>
			<input type="hidden" name="cycle[id][]" value="@{{#if id}}@{{ id }}@{{else}}new@{{/if}}">
			<div class="form-group">
				<label for="price">Price: </label>
				<input type="number" name="cycle[price][]" id="price" class="form-control" min="0" step="0.01" value="@{{ price }}" placeholder="0.00">
			</div>
			<div class="form-group">
				<label for="setup">Setup Fee: </label>
				<input type="number" name="cycle[setup][]" id="setup" class="form-control" min="0" step="0.01" value="@{{ setup }}" placeholder="0.00">
			</div>
			<div class="form-group">
				<label for="cycle">Cycle: </label>
				<select name="cycle[cycle][]" id="cycle" class="form-control">
					<option value="1" @{{#if (cond cycle "===" '1') }}selected@{{/if}}>One-Off</option>
					<option value="2" @{{#if (cond cycle "===" '2') }}selected@{{/if}}>Daily</option>
					<option value="3" @{{#if (cond cycle "===" '3') }}selected@{{/if}}>Weekly</option>
					<option value="4" @{{#if (cond cycle "===" '4') }}selected@{{/if}}>Fortnightly</option>
					<option value="5" @{{#if (cond cycle "===" '5') }}selected@{{/if}}>Monthly</option>
					<option value="6" @{{#if (cond cycle "===" '6') }}selected@{{/if}}>Every 2 Months</option>
					<option value="7" @{{#if (cond cycle "===" '7') }}selected@{{/if}}>Every 3 Months</option>
					<option value="8" @{{#if (cond cycle "===" '8') }}selected@{{/if}}>Every 4 Months</option>
					<option value="9" @{{#if (cond cycle "===" '9') }}selected@{{/if}}>Every 5 Months</option>
					<option value="10" @{{#if (cond cycle "===" '10') }}selected@{{/if}}>Every 6 Months</option>
					<option value="11" @{{#if (cond cycle "===" '11') }}selected@{{/if}}>Every 7 Months</option>
					<option value="12" @{{#if (cond cycle "===" '12') }}selected@{{/if}}>Every 8 Months</option>
					<option value="13" @{{#if (cond cycle "===" '13') }}selected@{{/if}}>Every 9 Months</option>
					<option value="14" @{{#if (cond cycle "===" '14') }}selected@{{/if}}>Every 10 Months</option>
					<option value="15" @{{#if (cond cycle "===" '15') }}selected@{{/if}}>Every 11 Months</option>
					<option value="16" @{{#if (cond cycle "===" '16') }}selected@{{/if}}>Every 12 Months</option>
					<option value="17" @{{#if (cond cycle "===" '17') }}selected@{{/if}}>Every 24 Months</option>
					<option value="18" @{{#if (cond cycle "===" '18') }}selected@{{/if}}>Every 36 Months</option>
				</select>
			</div>
		</div>
	</script>

	<script src="https://v2.b-cdn.uk/plugins/iCheck/icheck.min.js"></script>
	<script>
		$('#description').wysihtml5();

		$(document).on('click', '.addCycle', addCycle);

		function addCycle(values) {
			if (values === undefined) {
				values = {};
			}

			var $html = Handlebars.compile($('#cycle-template').html())(values);
			$('#cycles').append($html);

			return false;
		}

		$(document).on('click', '.removeCycle', function() {
			$(this).parents('.cycle').remove();

			return false;
		});

		$('.fileChecks').each(function() {
			var self = $(this),
				label = self.next(),
				label_text = label.text();

			label.remove();
			self.iCheck({
				checkboxClass: 'icheckbox_line-blue',
				insert: '<div class="icheck_line-icon"></div>' + label_text
			});
		});

		$('.fileChecks').on('ifChecked', function() {
			$(this).parent().removeClass('icheckbox_line-red').addClass('icheckbox_line-blue');
		});

		$('.fileChecks').on('ifUnchecked', function() {
			$(this).parent().removeClass('icheckbox_line-blue').addClass('icheckbox_line-red');
		});

		{{-- Logic to handle Package Cycles --}}
		@if (old('cycle.id'))
			@foreach (old('cycle.id') as $k => $id)
				addCycle({id: '{{ $id }}', price: '{{ old('cycle.price')[$k] }}', setup: '{{ old('cycle.setup')[$k] }}', cycle: '{{ old('cycle.cycle')[$k] }}'});
			@endforeach
		@elseif (count($plan->cycles))
			@foreach ($plan->cycles as $cycle)
				addCycle({id: '{{ $cycle->id }}', price: '{{ $cycle->price }}', setup: '{{ $cycle->fee }}', cycle: '{{ $cycle->cycle }}'});
			@endforeach
		@else
			addCycle();
		@endif

	</script>
@stop
