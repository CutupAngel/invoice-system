@extends('Auth.template')

@section('title', 'Reset Password')

@section('content')
    <p class="login-box-msg">
        Please enter your email address below.
    </p>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form method="post" action="email">
        {{ csrf_field() }}

        <div class="form-group {{ $errors->has('email') ? ' has-error' : '' }}">
            <input type="email" class="form-control" name="email" placeholder="{{ trans('auth.email') }}" value="{{ old('email') }}">
            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-btn fa-envelope"></i> Send Password Reset Link
            </button>
        </div>
    </form>
@endsection
