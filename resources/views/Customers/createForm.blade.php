@extends ('Common.template')

@section('title', 'Customers')

@section('page.title', 'Customers')
@section('page.subtitle')
	{{$type}}
@stop

@section('breadcrumbs')
	<li class="active">Create Customer</li>
@stop

@section('content')
	<form method="POST" action="/customers/{{ $customer->id }}">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">
		@if ($type === 'Edit')
			<input type="hidden" name="_method" value="PUT">
		@endif
 <div class="row">
	<div class="col-md-6">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title">
					{{ trans('backend.cust-details') }}
				</h3>
			</div>
			<div class="card-body">
				@if (count($errors) > 0)
					<div class="alert alert-error">
						<ul>
							@foreach($errors->all() as $error)
								<li>{{$error}}</li>
							@endforeach
						</ul>
					</div>
				@endif
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="col-lg-6 col-md-12">
								<div class="form-group">
									<label for="name">{{ trans('backend.cust-name') }}</label>
									<input type="text" class="form-control" required name="name" id="name" value="{{ old('name', $customer->name) }}">
								</div>
								<div class="form-group">
									<label for="website">{{ trans('backend.cust-web') }}</label>
									<input type="url" class="form-control" name="website" id="website" value="{{ old('website', $customer->mailingContact->address->website) }}">
								</div>
							</div>
							<div class="col-lg-6 col-md-12">
								<div class="form-group">
									<label for="business_name">{{ trans('backend.cust-business') }}</label>
									<input type="text" class="form-control" name="business_name" id="business_name" value="{{ old('business_name', $customer->mailingContact->address->business_name) }}">
								</div>
								<div class="form-group">
									<label for="address_1">{{ trans('backend.cust-address1') }}</label>
									<input type="text" class="form-control" required name="address_1" id="address_1" value="{{ old('address_1', $customer->mailingContact->address->address_1) }}">
								</div>
								<div class="form-group">
									<label for="address_2">{{ trans('backend.cust-address2') }}</label>
									<input type="text" class="form-control" name="address_2" id="address_2" value="{{ old('address_2', $customer->mailingContact->address->address_2) }}">
								</div>
								<div class="form-group">
									<label for="city">{{ trans('backend.cust-city') }}</label>
									<input type="text" class="form-control" required name="city" id="city" value="{{ old('city', $customer->mailingContact->address->city) }}">
								</div>
								<div class="form-group">
									<label for="county">{{ trans('backend.cust-state') }}</label>
									<select name="county" id="county" class="form-control" disabled>
										<option>{{ trans('backend.cust-select') }}</option>
									</select>
								</div>
								<div class="form-group">
									<label for="country">{{ trans('backend.cust-country') }}</label>
									<select name="country" id="country" class="form-control">
										<option disabled {{ old('country', $customer->mailingContact->address->country_id) === null ? 'selected' : '' }}></option>
										@foreach ($countries as $country)
											<option value="{{$country->id}}" {{ old('country', $customer->mailingContact->address->country_id) != $country->id ?: 'selected' }}>{{$country->name}}</option>
										@endforeach
									</select>
								</div>
								<div class="form-group">
									<label for="postal_code">{{ trans('backend.cust-postal') }}</label>
									<input type="text" class="form-control" required name="postal_code" id="postal_code" value="{{ old('postal', $customer->mailingContact->address->postal_code) }}">
								</div>
								<div class="form-group">
									<label for="phone">{{ trans('backend.cust-telephone') }}</label>
									<input type="tel" class="form-control" required name="phone" id="phone" value="{{ old('phone', $customer->mailingContact->address->phone) }}">
								</div>
								<div class="form-group">
									<label for="fax">{{ trans('backend.cust-fax') }}</label>
									<input type="tel" class="form-control" name="fax" id="fax" value="{{ old('fax', $customer->mailingContact->address->fax) }}">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

			<div class="col-md-6">
			 <div class="card">
				 <div class="card-header">
					 <h3 class="card-title">
						 {{ trans('backend.cust-logininfo') }}
					 </h3>
				 </div>
					<div class="panel panel-default">
						<div class="card-body">
							<div class="form-group">
								<label for="username">{{ trans('backend.cust-email') }}</label>
								<input type="email" class="form-control" required name="username" id="username" autocomplete="off" value="{{ old('username', $customer->username) }}">
							</div>
							<div class="form-group">
								<label for="password">{{ trans('backend.cust-password') }}</label>
								<input type="password" class="form-control" name="password" id="password" autocomplete="off" value="{{ old('password') }}" @if ($type == 'Edit') placeholder="Leave blank to keep the same" @else required @endif>
							</div>
							<div class="form-group">
								<label for="comment">{{ trans('backend.cust-notessecond') }}</label>
								<textarea class="form-control" rows="5" id="comment" name="comment">{{ old('comment', $notes) }}</textarea>
							</div>
							<div class="form-group">
								<label for="comment">{{ trans('backend.cust-credit') }}</label>
								<input type="text" class="form-control" name="credit"" autocomplete="off" value="{{ old('credit', $credit) }}">
							</div>
						</div>
				</div>
			</div>
		</div>
				<button class="btn btn-success float-right mb-3" type="submit">{{ trans('backend.cust-save') }}</button>
		</div>
	</div>
	</form>
@stop

@section('javascript')
	<script>
		$('#country').on('change', function() {
			var oldCounty = '{{ old('county', $customer->mailingContact->address->county_id) }}';
			var country = $(this).val();
			if (country.length === 0) {
				return false;
			}

			$('#county option').remove();

			$.ajax({
				url: '/helper/counties/' + country,
				dataType: 'json',
				success: function(counties) {
					$.each(counties, function(i, county) {
						$('#county').append($('<option></option>').attr('value', county.id).text(county.name));
					});

					$('#county').prop('disabled', false);

					if (oldCounty.length !== 0) {
						$('#county').val(oldCounty);
					}
				}
			});
		}).trigger('change');
	</script>
@stop
