<li class="nav-item dropdown">
  <a class="nav-link" data-toggle="dropdown" href="#">
    <i class="fa fa-bell"></i>
    <span class="badge badge-warning navbar-badge">{{$notifications::count()}}</span>
  </a>
  <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
    <li class="dropdown-item">You have {{$notifications::count()}} {{str_plural('notification', $notifications::count())}}</li>
    <li>
      <!-- inner menu: contains the actual data -->
      <ul class="dropdown-item">
        @foreach ($notifications::get() as $notification)
          <li class="dropdown-item">
            <a href="{{$notification['link']}}" class="dropdown-link">
              {{$notification['string']}}
            </a>
          </li>
          <div class="dropdown-divider"></div>
        @endforeach
      </ul>
    </li>
  </ul>
</li>
