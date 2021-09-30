<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<title>{{$site('name')}} | @yield('title', 'Home')</title>
<meta name="description" content="Welcome to {{$site('name')}}'s Shopping Cart. Powered by BillingServ.'">
<link href="https://v2.b-cdn.uk/new-theme/css/aa.css" rel="stylesheet" type="text/css" />
<link rel='icon' href='https://v2.b-cdn.uk/app-icons/favicon.ico' type='image/x-icon'>
<script src="https://kit.fontawesome.com/9745017368.js" crossorigin="anonymous"></script>
@yield('css')
@if($site('customCSS'))
        <link rel="stylesheet" href="{{config('app.CDN')}}{!! $site('customCSS') !!}">
@endif
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
a.dropdown-item {
  display: block;
  padding: 6px 36px;
  clear: both;
  font-weight: 400;
  line-height: 1.42857143;
  color: #333;
  white-space: nowrap;
}
html {
  height: 100%;
  box-sizing: border-box;
}

*,
*:before,
*:after {
  box-sizing: inherit;
}

body {
  position: relative;
  margin: 0;
  padding-bottom: 6rem;
  min-height: 100%;
}

.footer {
  position: absolute;
  right: 0;
  bottom: 0;
  left: 0;
  text-align: center;
  height: 82px;
}
.navbar-left {
  padding-top: 28px !important;
}
@media (min-width: 767px) {
.navbar-brand {
  float: left !important;
  height: 60px !important;
  padding: 23px 15px 1px 0px !important;
  font-size: 21px !important;
  line-height: 20px !important;
  padding-left: 25px !important;
  position: relative !important;
  }
.footer {
  position: absolute !important;
  right: 0 !important;
  bottom: 0 !important;
  left: 0 !important;
  text-align: center !important;
  height: 87px !important;
  }
.footer .left p {
  float: left !important;
  position: relative !important;
  top: 7px !important;
  margin-right: 20px !important;
  color: #fff !important;
  margin-left: 80px !important;
    }
}
@media screen and (max-width: 767px) {
  .navbar-brand {
    float: left !important;
    height: 60px !important;
    padding: 23px 15px 1px 0px !important;
    font-size: 21px !important;
    line-height: 20px !important;
    padding-left: 25px !important;
    position: relative !important;
    }
}
@media (min-width: 992px) {
.navbar-brand {
    float: left !important;
    height: 62px !important;
    padding: 14px 15px 1px 0px !important;
    font-size: 21px !important;
    line-height: 20px !important;
    padding-left: 25px !important;
  }
}
@media only screen
   and (min-device-width : 320px)
   and (max-device-width : 480px) {
.navbar-brand {
  float: left !important;
  height: 60px !important;
  padding: 23px 15px 1px 0px !important;
  font-size: 21px !important;
  line-height: 20px !important;
  padding-left: 40px !important;
  position: relative !important;
  }
.footer {
  position: absolute !important;
  right: 0 !important;
  bottom: 0 !important;
  left: 0 !important;
  text-align: center !important;
  height: 100px !important;
  }
.footer .left p {
  float: left !important;
  position: relative !important;
  top: 7px !important;
  margin-right: 20px !important;
  color: #fff !important;
  margin-left: 35px !important;
  }
  .navbar-brand-white {
    color: #fff;
    text-align: left;
    font-weight: 200;
    font-size: small;
    float: left;
    padding: 27px 15px;
    line-height: 20px;
  }
  aside.col-md-4.total-sidebar-ben {
    padding: 0;
    background: #0071BC;
    border-radius: 10px;
    width: 95%;
    position: relative;
    margin-top: 25px;
    margin-left: auto;
    margin-right: auto;
  }
}
</style>
</head>
        <body>
                        @if($site('headerHTML'))
                                {!! $site('headerHTML') !!}
                        @else
                                <header class="navbar navbar-default navbar-fixed-top">
                                                @include('Common.frontendHeader')
                                </header>
                        @endif
                        <div class="space-three"></div>
                        <div class="space-three"></div>
                        <div class="space-three"></div>
                        <div class="container">
                                  @yield('content')
                              </div>
                        <div class="space-three"></div>
                        @if($site('footerHTML'))
                                {!! $site('footerHTML') !!}
                        @else
                        <footer class="footer"><div class="container">@include('Common.frontendFooter')</div></footer>
                        @endif

                        <script src="https://v2.b-cdn.uk/plugins/jQuery/jQuery-2.1.4.min.js"></script>
                        <script src="https://v2.b-cdn.uk/new-theme/js/aa.min.js"></script>


                @yield('js')


    <script>
        $(document).ready( function () {
            $('#ajax-cart').on('click', 'a.remove-item', function() {
                var block = $(this).closest('table.table');

                querryFilter(block, {
                    'rowid': $(this).data('rowid'),
                    'action': 'remove',
                });
            });

            $('#ajax-cart').on('change', '.ajax-quantity', function() {
                var block = $(this).closest('table.table');

                querryFilter(block, {
                    'rowid': $(this).data('rowid'),
                    'qty': $(this).val(),
                    'action': 'update',
                });
            });
        });

        var querryFilter = function (block, param) {

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: block.data('url'),
                type: 'POST',
                data: param,
                success: function (data) {
                    $('#ajax-cart').html(data.view);
                    $('#ajax-total-bascket').html(data.haderbasckettotal);
                }
            });
        };

    </script>
        </body>
</html>
