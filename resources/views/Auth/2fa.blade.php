@extends('Auth.template')

@section('title', 'Two-Factor Authentication')

@section('content')
	@if (count($errors) > 0)
		<div class="alert alert-danger alert-dismissible">
			<button type="button" class="close" data-dismiss="alert">×</button>
			@foreach($errors->all() as $error)
				<i class="icon fa fa-ban"></i>{{$error}}<br>
			@endforeach
		</div>
	@endif

	<form action="/auth/2fa" method="post">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">
		<div class="form-group has-feedback">
			<input type="text" class="form-control" name="2fa" placeholder="2FA Code" value="{{ old('2fa') }}">
			<span class="fa fa-lock form-control-feedback"></span>
		</div>
		<button type="submit" class="btn btn-primary btn-block btn-flat">Check Code</button>
	</form>
@stop
