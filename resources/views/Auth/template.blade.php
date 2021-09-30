<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $site('name') }} | @yield('title', 'Login')</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="https://v2.b-cdn.uk/components/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://v2.b-cdn.uk/dist/css/AdminLTE.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="https://v2.b-cdn.uk/plugins/iCheck/square/blue.css">
    <!-- Adding Favicon & App Icons -->
    <link rel="shortcut icon" type="image/ico" href="https://v2.b-cdn.uk/app-icons/favicon.ico">
    <link rel="apple-touch-icon" type="image/png" href="https://v2.b-cdn.uk/app-icons/BSV2AppIcon.png"><!-- iPhone -->
    <link rel="apple-touch-icon" type="image/png" sizes="72x72" href="https://v2.b-cdn.uk/app-icons/BSV2AppIcon.png"><!-- iPad -->
    <link rel="apple-touch-icon" type="image/png" sizes="114x114" href="https://v2.b-cdn.uk/app-icons/BSV2AppIcon.png"><!-- iPhone4 -->
    <link rel="icon" type="image/png" href="https://v2.b-cdn.uk/app-icons/BSV2AppIcon.png"><!-- Opera Speed Dial, at least 144×114 px -->


    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="hold-transition login-page">
    <div class="login-box">
      <div class="login-logo">
      @if ($site('logo'))
        <b><img src="{{config('app.CDN')}}{{ $site('logo') }}" width="250" alt="{{$site('name')}}"></b>
      @else
        <b>{{ $site('name') }}</b>
      @endif
      </div><!-- /.login-logo -->
      <div class="login-box-body">
        @yield('content')
      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->

    <!-- jQuery 2.1.4 -->
    <script src="https://v2.b-cdn.uk/components/jquery/jquery.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="https://v2.b-cdn.uk/components/bootstrap/js/bootstrap.min.js"></script>
    <!-- iCheck -->
    <script src="https://v2.b-cdn.uk/plugins/iCheck/icheck.min.js"></script>
    <script>
      $(function () {
        $('input').iCheck({
          checkboxClass: 'icheckbox_square-blue',
          radioClass: 'iradio_square-blue',
          increaseArea: '20%' // optional
        });
      });
    </script>
    @yield('javascript')
  </body>
</html>
