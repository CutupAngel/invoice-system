<div class="topbar topbar-dark bg-dark">
        <div class="container">
          <div class="navbar-brand-white">
          <i class="fa fa-phone" aria-hidden="true"></i>
            &nbsp;
      @php
        $user = null;
        if(Auth::user()) {
          $user = Auth::user();
        }
      @endphp
      @php
        $userDefault = App\User::orderBy('id', 'asc')->first();
      @endphp
        {{ @$userDefault->defaultContact->address->phone}}
      </div>
      </div><!-- center -->
    </div><!-- header-top -->

    <div class="container">
    <div class="navbar-header">
    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
    <span class="sr-only">Toggle navigation</span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    </button>
    @if ($site('logo'))
    <img src="{{config('app.CDN')}}{{ $site('logo') }}" class="navbar-brand" alt="{{$site('name')}}">
    @else

    @endif
    </div>
    <div id="navbar" class="collapse navbar-collapse">
    <ul class="nav navbar-nav">
      <li><a href="/home">{{ trans('frontend.nav-home') }}</a></li>
      <li><a href="/order">{{ trans('frontend.nav-order') }}</a></li>
      <li><a href="/auth/login">{{ trans('frontend.nav-login') }}</a></li>
      <!--<li><a href="/support">{{ trans('frontend.nav-support') }}</a></li>--></ul>
    </ul>
    <ul class="nav navbar-nav navbar-right">
      <li>
          <a href="{{ route('view-cart') }}" id="ajax-total-bascket">
              @include('Common.cart.header-total-bascket')
          </a>
      </li>

      <li class="dropdown" style="height:58px;">
          <a href="#" data-toggle="dropdown" class="dropdown-toggle">
              <i class="fa fa-money" aria-hidden="true"></i>
              &nbsp;&nbsp;
              <span>{{ trans('frontend.nav-currency') }}: {{$currency->short_name}} {!! $currency->symbol !!}</span>
              <i class="fa fa-sort-down"></i>
          </a>
          @if (isset($cart) && isset($cart['availableCurrencies']))
          <div class="dropdown-menu" style="background-color:#373f50;">
              @foreach($cart['availableCurrencies'] as $currency2)
                @if(!empty($basketGrendTotal))
                  <a class="dropdown-item" style="color:white;" href="/currency/{{$currency2->id}}">{{$currency2->short_name}} {!! $currency2->symbol !!}{{number_format($basketGrendTotal / $default_currency->conversion * $currency2->conversion,2)}}
                @else
                  <a class="dropdown-item" style="color:white;" href="/currency/{{$currency2->id}}">{{$currency2->short_name}} {!! $currency2->symbol !!}{{number_format($cart['grandTotal'] / $default_currency->conversion * $currency2->conversion,2)}}
                @endif
              </a><br>
              @endforeach
          </div>
          @endif
      </li>
      <li class="dropdown" style="height:58px;">
          <a href="#" data-toggle="dropdown" class="dropdown-toggle">
              <i class="fa fa-user" aria-hidden="true"></i>
              &nbsp;&nbsp;
              <span>{{ trans('frontend.nav-myaccount') }}</span>
              <i class="fa fa-sort-down"></i>
          </a>
          <div class="dropdown-menu" style="background-color:#373f50;">
              @if(Auth::User())
              <a class="dropdown-item" style="color:white;" href="/"><div>{{ trans('frontend.nav-dashboard') }}</div></a>
              <a class="dropdown-item" style="color:white;" href="/auth/logout"><div>{{ trans('frontend.nav-logout') }}</div></a>
              @else
              <a class="dropdown-item" style="color:white;" href="/auth/login?url={{Request::url()}}"><div>{{ trans('frontend.nav-login') }}</div></a>
              <a class="dropdown-item" style="color:white;" href="/auth/register"><div>{{ trans('frontend.nav-register') }}</div></a>
              @endif
          </div>
        </li>
    </div>
    </div>
