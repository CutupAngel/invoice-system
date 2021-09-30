@extends('Auth.template')

@section('title', 'Login')

@section('content')
	<p class="login-box-msg">{!! trans('backend.login-welcome', array('sitename' => $site('name'))) !!}</p>

	@if (session('status'))
			<div class="alert alert-success">
					{{ session('status') }}
			</div>
	@endif

	@if (count($errors) > 0)
		<div class="alert alert-danger alert-dismissible">
			<button type="button" class="close" data-dismiss="alert">×</button>
			@foreach($errors->all() as $error)
				<i class="icon fa fa-ban"></i>{{$error}}<br>
			@endforeach
		</div>
	@endif

	<form action="/auth/login" method="post">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">
		<div class="form-group has-feedback">
			<input type="text" class="form-control" name="username" placeholder="{{ trans('backend.login-email') }}" value="{{ old('username') }}">
			<span class="glyphicon glyphicon-sunglasses form-control-feedback"></span>
		</div>
		<div class="form-group has-feedback">
			<input type="password" class="form-control" name="password" placeholder="{{ trans('backend.login-password') }}">
			<span class="glyphicon glyphicon-lock form-control-feedback"></span>
		</div>
		<div class="row">
			<div class="col-xs-8">
				<div class="checkbox icheck">
					<label>
						<input type="checkbox" name="remember" value="Y" {{ (old('remember') ? 'checked' : '') }}> {{ trans('backend.login-rememberme') }}
					</label>
				</div>
			</div><!-- /.col -->
			<div class="col-xs-4">
				<button type="submit" class="btn btn-primary btn-block btn-flat">{{ trans('backend.login-signin') }}</button>
			</div><!-- /.col -->
		</div>
</form>

	<a href="/auth/password/reset">{{ trans('backend.login-resetpass') }}</a><br>
<div class="login-footer">
	<a href="/auth/register" class="text-center">{{ trans('backend.login-signup') }}</a>
</div>
@stop
