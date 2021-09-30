@extends('Auth.template')

@section('title', 'Register')

@section('content')
  <p class="login-box-msg">{!! trans('backend.register-welcome', array('sitename' => $site('name'))) !!}</p>

  @if (count($errors) > 0)
		<div class="alert alert-danger alert-dismissible">
			<button type="button" class="close" data-dismiss="alert">×</button>
			@foreach($errors->all() as $error)
				<i class="icon fa fa-ban"></i>{{$error}}<br>
			@endforeach
		</div>
	@endif

  <form action="/auth/register" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="form-group has-feedback">
      <input type="text" class="form-control" name="name" placeholder="{{ trans('backend.register-fullname') }}" value="{{ old('name') }}">
      <span class="glyphicon glyphicon-user form-control-feedback"></span>
    </div>
    <div class="form-group has-feedback" hidden>
      <input type="text" class="form-control" name="username" placeholder="{{ trans('backend.register-username') }}" value="{{ old('username') }}">
      <span class="glyphicon glyphicon-sunglasses form-control-feedback"></span>
    </div>
    <div class="form-group has-feedback">
      <input type="email" class="form-control" name="email" placeholder="{{ trans('backend.register-email') }}" value="{{ old('email') }}">
      <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
    </div>
    <div class="form-group has-feedback">
      <input type="password" class="form-control" name="password" placeholder="{{ trans('backend.register-password') }}">
      <span class="glyphicon glyphicon-lock form-control-feedback"></span>
    </div>
    <div class="form-group has-feedback">
      <input type="password" class="form-control" name="password_confirmation" placeholder="{{ trans('backend.register-retypepass') }}">
      <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
    </div>
    <div class="form-group has-feedback">
      <input type="text" class="form-control" name="phone" placeholder="{{ trans('backend.register-contact-phone') }}" value="{{ old('phone') }}">
      <span class="glyphicon glyphicon-phone form-control-feedback"></span>
    </div>
    <div class="form-group has-feedback">
      <input type="text" class="form-control" name="address_1" placeholder="{{ trans('backend.register-contact-address_1') }}" value="{{ old('address_1') }}">
      <span class="glyphicon glyphicon-home form-control-feedback"></span>
    </div>
    <div class="form-group has-feedback">
      <input type="text" class="form-control" name="city" placeholder="{{ trans('backend.register-contact-city') }}" value="{{ old('city') }}">
      <span class="glyphicon glyphicon-map-marker form-control-feedback"></span>
    </div>
    <div class="form-group has-feedback">
      <input type="text" class="form-control" name="postal_code" placeholder="{{ trans('backend.register-contact-postal_code') }}" value="{{ old('postal_code') }}">
      <span class="glyphicon glyphicon-send form-control-feedback"></span>
    </div>
    <div class="form-group has-feedback">
      <select name="country_id" id="country_id" class="form-control">
        @foreach ($countries as $country)
          <option value="{{$country->id}}" @php if(old('country_id') == $country->id) echo 'selected'; @endphp>{{$country->name}}</option>
        @endforeach
      </select>
      <span class="glyphicon glyphicon-map-marker form-control-feedback"></span>
    </div>
    <div class="form-group has-feedback">
      <select name="county_id" id="county_id" class="form-control" disabled>
        <option>Select County</option>
      </select>
      <span class="glyphicon glyphicon-map-marker form-control-feedback"></span>
    </div>
    <div class="row">
      <div class="col-xs-8">
        <div class="checkbox icheck">
          <label>
            <input type="checkbox" name="confirm_terms" @php if(old('confirm_terms')) echo 'checked'; @endphp> {{ trans('backend.register-terms') }}
          </label>
        </div>
      </div><!-- /.col -->
      <div class="col-xs-4">
        <button type="submit" class="btn btn-primary btn-block btn-flat">{{ trans('backend.register-signup') }}</button>
      </div><!-- /.col -->
    </div>
  </form>

  <a href="/auth/login" class="text-center">{{ trans('backend.register-account') }}</a>
@stop

@section('javascript')
	<script>
  $(function () {
		$('#country_id').on('change', function() {
			var country = $(this).val();
			if (country.length === 0) {
				return false;
			}

			$('#county option').remove();

			$.ajax({
				url: '/helper/counties/' + country,
				dataType: 'json',
				success: function(counties) {
          $('#county_id').empty();
					$.each(counties, function(i, county) {
						$('#county_id').append($('<option></option>').attr('value', county.id).text(county.name));
					});
					$('#county_id').prop('disabled', false);
          $('#county_id').val("{{ old('county_id') }}");
				}
			});
		}).trigger('change');
  });
	</script>
@stop
