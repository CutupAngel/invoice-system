@extends('Auth.template')

@section('title', 'Reset Password')

@section('content')
    <p class="login-box-msg">
        Compelete your password reset by filling out the form below.
    </p>

    <form method="POST" action="{{ url('auth/password/reset') }}">
        {{ csrf_field() }}
        <input type="hidden" name="token" value="{{$token}}">

        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
            <input type="email" class="form-control" name="email" placeholder="{{ trans('auth.email') }}" value="{{ old('email', $email) }}">

            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
        </div>

        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
            <input type="password" class="form-control" placeholder="Password" name="password">

            @if ($errors->has('password'))
                <span class="help-block">
                    <strong>{{ $errors->first('password') }}</strong>
                </span>
            @endif
        </div>

        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
            <input type="password" class="form-control" placeholder="Confirm Password" name="password_confirmation">

            @if ($errors->has('password_confirmation'))
                <span class="help-block">
                    <strong>{{ $errors->first('password_confirmation') }}</strong>
                </span>
            @endif
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-btn fa-refresh"></i> Reset Password
            </button>
        </div>
    </form>
@endsection
