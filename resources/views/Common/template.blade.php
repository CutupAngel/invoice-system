<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{$site('name')}} | @yield('title', 'Dashboard')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://v2.b-cdn.uk/new-admin/plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bbootstrap 4 -->
    <link rel="stylesheet" href="https://v2.b-cdn.uk/new-admin/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="https://v2.b-cdn.uk/new-admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- JQVMap -->
    <link rel="stylesheet" href="https://v2.b-cdn.uk/new-admin/plugins/jqvmap/jqvmap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://v2.b-cdn.uk/new-admin/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="https://v2.b-cdn.uk/new-admin/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="https://v2.b-cdn.uk/new-admin/plugins/daterangepicker/daterangepicker.css">
    <!-- summernote -->
    <link rel="stylesheet" href="https://v2.b-cdn.uk/new-admin/plugins/summernote/summernote-bs4.css">
    <!-- summernote -->
    <link rel="stylesheet" href="https://v2.b-cdn.uk/new-admin/plugins/summernote/summernote-bs4.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://v2.b-cdn.uk/new-admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://v2.b-cdn.uk/new-admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <!-- Morris chart -->
    <link rel="stylesheet" href="https://v2.b-cdn.uk/new-admin/plugins/morris/morris.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <!-- Adding Favicon & App Icons -->
    <link rel="shortcut icon" type="image/ico" href="https://v2.b-cdn.uk/app-icons/favicon.ico">
    <link rel="apple-touch-icon" type="image/png" href="https://v2.b-cdn.uk/app-icons/BSV2AppIcon.png"><!-- iPhone -->
    <link rel="apple-touch-icon" type="image/png" sizes="72x72" href="https://v2.b-cdn.uk/app-icons/BSV2AppIcon.png"><!-- iPad -->
    <link rel="apple-touch-icon" type="image/png" sizes="114x114" href="https://v2.b-cdn.uk/app-icons/BSV2AppIcon.png"><!-- iPhone4 -->
    <link rel="icon" type="image/png" href="https://v2.b-cdn.uk/app-icons/BSV2AppIcon.png"><!-- Opera Speed Dial, at least 144×114 px -->
    <!-- Userreport -->
    <script src="https://sak.userreport.com/billingserv/launcher.js" async id="userreport-launcher-script"></script>
    <meta name="userreport:mediaId" value="27890337-856a-4afe-8456-017bca6faf26"/>

    @yield('css')

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <div class="wrapper">
  <!-- Navbar -->
      <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
          </li>
        </ul>
        @if (Auth::user())
            @if (Auth::User()->isAdmin() || Auth::User()->isClient() || Auth::User()->isStaff())
                  <!-- SEARCH FORM -->
                  <form class="form-inline ml-2" action="#" method="get" id="searchForm">
                    <div class="input-group input-group-sm">
                      <input type="text" name="q" class="form-control form-control-navbar left" placeholder="Search...">
                      <div class="input-group-append">
                        <button type="button" name="search" id="search-btn" class="btn btn-navbar" onclick="SubmitSearch();"><i class="fa fa-search"></i></button>
                      </div>
                    </div>
                  </form>
                  @include('Common.search')
                  <!-- SEARCH FORM End -->
            @endif
        @endif
              <!-- Right navbar links -->
              <ul class="navbar-nav ml-auto">
                <!-- Messages Dropdown Menu -->
                <li class="nav-item dropdown">
                  <ul class="dropdown-menu">
                    <li>
                    <!-- inner menu: contains the actual data -->
                    <ul class="menu">
                    </ul>
                  </li>
                  <li class="footer"><a href="#">See All Messages</a></li>
                </ul>
                <!-- Notifications: style can be found in dropdown.less -->
                @include('Common.notifications')
                <!-- User Account: style can be found in dropdown.less -->
              </li>
              <li class="nav-item dropdown user user-menu">
                <a href="#" class="nav-link" data-toggle="dropdown">
                   @if (Auth::check())
<img src="{{ Gravatar::get(Auth::User()->email, ['secure'=>true]) }}" class="user-image" alt="User Image">
@endif
                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header">
                     @if (Auth::check())
<img src="{{ Gravatar::get(Auth::User()->email, ['secure'=>true]) }}" class="img-circle" alt="User Image">
@endif
                    <p>
                      <b>{{Auth::User()->name}}</b><br>
                    </p>
                  </li>
                  <!-- Menu Body -->
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <div class="float-left">
					@if (Auth::User()->isCustomer())
					  <a href="/settings/myaccount" class="btn btn-default btn-flat">{{ trans('backend.cb-myaccount') }}</a>
					@else
                      <a href="/settings/my-account" class="btn btn-default btn-flat">{{ trans('backend.cb-myaccount') }}</a>
				    @endif
                    </div>
                    <div class="float-right">
                      @if (Auth::User()->isImpersonating())
                        <a href="/stopImpersonating" class="btn btn-default btn-flat">{{ trans('backend.cb-returnadmin') }}</a>
                      @else
                        <a href="/auth/logout" class="btn btn-default btn-flat">{{ trans('backend.cb-signout') }}</a>
                      @endif
                    </div>
                  </li>
                </ul>
              </li>
              <!-- Control Sidebar Toggle Button -->
            </ul>
      </nav>
      <!-- Main Sidebar Container -->
      <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="/" class="brand-link text-center">
          <!-- logo for regular state and mobile devices -->
          <span class="brand-text font-weight-light">@if ($site('logo'))
            <b><img src="{{config('app.CDN')}}{{ $site('logo') }}" width="75%" alt="{{$site('name')}}"></b>
          @else
            <b>{{ $site('name') }}</b>
          @endif</span>
        </a>
        <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class
             with font-awesome or any other icon font library -->
            <!--<li class="header">{{ trans('backend.cb-main') }}</li>-->
            @include('Common.nav')

            @if (Auth::user())
                @if (Auth::User()->isClient() || Auth::User()->isAdmin() || Auth::User()->isStaff())
                  <!--<li class="header">{{ trans('backend.cb-support') }}</li>-->
                  @include('Common.supportNav')
                @endif
            @endif
          </ul>
        </nav>
        <!-- /.sidebar -->
      </div>
    </aside>

        <!-- Content Wrapper. Contains page content -->
         <div class="content-wrapper">
           @if (Auth::User()->isImpersonating())
             <div class="alert alert-warning" style="border-radius: 0px">
               You are currently logged in as a client. <a href="/stopImpersonating"><b>Click here to return to the admin panel.</b>
             </div>
           @endif
           <!-- Content Header (Page header) -->
           <div class="content-header">
             <div class="container-fluid">
               <div class="row mb-2">
                 <div class="col-sm-6">
                   <h1 class="m-0 text-dark">
                     @yield('page.title')
                     <small>@yield('page.subtitle')</small>
                   </h1>
                 </div><!-- /.col -->
                 <div class="col-sm-6">
                   <ol class="breadcrumb float-sm-right">
                     <li class="breadcrumb-item"><a href="/">Home</a></li>
                     <li class="breadcrumb-item active">@yield('breadcrumbs')</li>
                   </ol>
                 </div><!-- /.col -->
               </div><!-- /.row -->
             </div><!-- /.container-fluid -->
           </div>
           <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
          <div class="container-fluid">
          @yield('content')
        </section><!-- /.content -->
      </div>
    </div><!-- /.content-wrapper -->

      <footer class="main-footer">
        <div class="float-right hidden-xs">
          <b>Version:</b> v2.1.8 02-06-2021
        </div>
        <div class="float-left"><strong>Copyright &copy; 2014-21 <a href="https://www.billingserv.com">BaseServ Limited</a>.</strong> All rights reserved.</div>
        <div class="text-center">You are connected to {{ $server }}. Please mention this in support requests.</div>
      </footer>

      <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
    </div><!-- ./wrapper -->
    <!-- jQuery -->
<script src="https://v2.b-cdn.uk/new-admin/plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="https://v2.b-cdn.uk/new-admin/plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="https://v2.b-cdn.uk/new-admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="https://v2.b-cdn.uk/new-admin/plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="https://v2.b-cdn.uk/new-admin/plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="https://v2.b-cdn.uk/new-admin/plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="https://v2.b-cdn.uk/new-admin/plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="https://v2.b-cdn.uk/new-admin/plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="https://v2.b-cdn.uk/new-admin/plugins/moment/moment.min.js"></script>
<script src="https://v2.b-cdn.uk/new-admin/plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="https://v2.b-cdn.uk/new-admin/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="https://v2.b-cdn.uk/new-admin/plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="https://v2.b-cdn.uk/new-admin/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="https://v2.b-cdn.uk/new-admin/js/adminlte.js"></script>
<!-- Morris.js charts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="https://v2.b-cdn.uk/new-admin/plugins/morris/morris.min.js"></script>
<!-- BaseServ JS Files -->
<script src="https://v2.b-cdn.uk/dist/js/billingserv.js"></script>
<!-- Handlebars -->
<script src="https://v2.b-cdn.uk/components/handlebars/handlebars.js"></script>
<script src="https://v2.b-cdn.uk/dist/js/handlebarsHelpers.js"></script>
<!-- DataTables -->
<script src="https://v2.b-cdn.uk/new-admin/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="https://v2.b-cdn.uk/new-admin/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://v2.b-cdn.uk/new-admin/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="https://v2.b-cdn.uk/new-admin/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script>
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
</script>
    @yield('javascript')
    <script>
    var url = window.location;
    const allLinks = document.querySelectorAll('.nav-item a');
    const currentLink = [...allLinks].filter(e => {
      return e.href == url;
    });

    for(var x = 0; x <= currentLink.length; x++)
    {
        try {
        currentLink[x].classList.add("active");
        currentLink[x].closest(".nav-treeview").style.display="block";
        currentLink[x].closest(".nav-treeview").classList.add("active");
      }
      catch(err) {
      }
    }


    </script>

    <script>

    $('#searchForm input').on('keypress',function(e) {
        if(e.which == 13) { //handle enter key
             e.preventDefault();
             SubmitSearch();
        }
    });

    var timeout;
    function SubmitSearch()
    {
        loadResults($('#searchForm input').val());
    }

    function loadResults(query) {
      clearTimeout(timeout);
      timeout = setTimeout(function() {
        $.get('/helper/search', {'q': query}, populateResults);
      }, 300);
    }

    function populateResults(results) {
      results = JSON.parse(results);

      if(Object.keys(results).length > 0) {
        $("#div_search_result").html('');
        var resultList = '<select id="search_result" class="form-control">';
        $.each(results, function(index, val) {
          resultList += '<option value="' + val.url + '">' + val.text + '</option>';
        });
        resultList += '</select>';
        resultList += '<div class="input-group-append"><button class="btn btn-success" onclick="NavigateResult();">Go</button></div>';
        $("#div_search_result").html(resultList);
      } else {
        $("#div_search_result").html('No Result Found.');
      }

      $('.searchResult').slideDown();
    }

    function NavigateResult()
    {
        window.location.href = $("#search_result").val();
    }
    </script>
  </body>
</html>
