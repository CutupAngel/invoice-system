<div id="billingInformation">
	<div class="row">
		<div class="col-md-4">
			<h2>{{ trans('frontend.chk-billing') }}</h2>
		</div>
		<div class="col-md-8 hidden-xs"></div>
	</div>
	<div class="row">
		<div class="col-md-12 directions">
			<span>{{ trans('frontend.chk-billingsub') }}</span>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6">
			<input class="form-control" type="text" name="billingInfo[firstname]" placeholder="{{ trans('frontend.chk-firstname') }}" value="{{ $user && isset($firstname) ? $firstname : old('firstname') }}" required>
		</div>
		<div class="col-md-6">
			<input class="form-control" type="text" name="billingInfo[lastname]" placeholder="{{ trans('frontend.chk-lastname') }}" value="{{ $user && isset($lastname) ? $lastname : old('lastname') }}" required>
		</div>
	</div>
	{{--
	<div class="row">
		<div class="col-md-6">
			<input class="form-control" type="text" name="billingInfo[company]" placeholder="{{ trans('frontend.chk-companyname') }}" value="{{ old('company') }}">
		</div>

	</div>
	--}}
	<div class="row">
		{{--
		<div class="col-md-6">
			<input class="form-control" type="text" name="billingInfo[fax]" placeholder="{{ trans('frontend.chk-fax') }}" value="{{ old('fax') }}">
		</div>
		--}}
		<div class="col-md-6">
			<input class="form-control" type="text" name="billingInfo[phone]" placeholder="{{ trans('frontend.chk-phone') }}" value="{{ $user && isset($contact->phone) ? $contact->phone : old('phone') }}" required>
		</div>
		<div class="col-md-6">
			<input class="form-control" type="text" name="billingInfo[email]" placeholder="{{ trans('frontend.chk-email') }}" value="{{ $user && isset($contact->email) ? $contact->email : old('email') }}" required>
		</div>
	</div>
</div>
<div id="billingAddress">
	<div class="row">
		<div class="col-md-4">
			<h2>{{ trans('frontend.chk-billingaddress') }}</h2>
		</div>
		<div class="col-md-8 hidden-xs"></div>
	</div>
	<div class="row">
		<div class="col-md-12 directions">
			<span>{{ trans('frontend.chk-billingaddresssub') }}</span>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6">
			<input class="form-control" type="text" name="billingAddress[address1]" placeholder="{{ trans('frontend.chk-address1') }}" value="{{ $user && isset($contact->address_1) ? $contact->address_1 : old('address1') }}" required>
		</div>
		<div class="col-md-6">
			<input class="form-control" type="text" name="billingAddress[address2]" placeholder="{{ trans('frontend.chk-address2') }}" value="{{ $user && isset($contact->address_2) ? $contact->address_2 : old('address2') }}">
		</div>
	</div>
	<div class="row">
		<div class="col-md-6">
			<input class="form-control" type="text" name="billingAddress[address3]" placeholder="{{ trans('frontend.chk-address3') }}" value="{{ $user && isset($contact->address_3) ? $contact->address_3 : old('address3') }}">
		</div>
		<div class="col-md-6">
			<input class="form-control" type="text" name="billingAddress[address4]" placeholder="{{ trans('frontend.chk-address4') }}" value="{{ $user && isset($contact->address_4) ? $contact->address_4 : old('address4') }}">
		</div>
	</div>
	<div class="row">
		<div class="col-md-3">
			<input class="form-control" type="text" name="billingAddress[city]" placeholder="{{ trans('frontend.chk-city') }}" value="{{ $user && isset($contact->city) ? $contact->city : old('city') }}" required>
		</div>
		<div class="col-md-3">
			<div class="input-prepend">
				<select class="form-control" name="billingAddress[region]" data-region="{{ $user && isset($contact->county_id) ? $contact->county_id : '' }}" required>
					<option value="">Region</option>
				</select>
			</div>
		</div>
		<div class="col-md-3">
			<div class="input-prepend">
				<select class="form-control" name="billingAddress[country]" required>
					<option value="">Country</option>
					@foreach ($countries as $country)
						<option value="{{$country->id}}"{{ $user && isset($contact->country_id) && $contact->country_id == $country->id ? ' selected' : '' }}>{{ $country->name }}</option>
					@endforeach
				</select>
			</div>
		</div>
		<div class="col-md-3">
			<input class="form-control" type="text" name="billingAddress[zip]" placeholder="{{ trans('frontend.chk-postalcode') }}" value="{{ $user && isset($contact->postal_code) ? $contact->postal_code : old('zip') }}" required>
		</div>
	</div>
</div>
<div class="row">
		<div class="col-md-12">
			<h2>Are you a company?</h2>
		</div>
	<div class="col-md-6">
    <select class="form-control" name="companyVat" id="companyVat">
      <option value="no" @php if(Auth::check() && Auth::User()->vat_number == '') echo 'selected'; @endphp>No</option>
      <option value="yes" @php if(Auth::check() && Auth::User()->vat_number != '') echo 'selected'; @endphp>Yes</option>
    </select>
	</div>
	<div class="col-md-6">
		<input class="form-control" name="vatNumber" id="vatNumber" placeholder="VAT Number" style="display:none;" value="@php if(Auth::check() && Auth::User()->vat_number != '') echo Auth::User()->vat_number; @endphp" />
		<input type="hidden" id="vatNumberValidated" value="no">
		<input type="hidden" id="useTax" name="useTax" value="yes">
	</div>
</div>
