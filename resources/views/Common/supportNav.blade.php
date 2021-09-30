{{-- Client Navigation --}}
@if (Auth::User()->isClient() || Auth::User()->isAdmin() || Auth::User()->isStaff())
<li class="nav-item has-treeview">
  <a href="#" class="nav-link">
    <i class="nav-icon fa fa-life-ring"></i>
    <p>{{ trans('backend.cb-billingservsupport') }}
      <i class="right fa fa-angle-left"></i>
    </p>
  </a>
  <ul class="nav nav-treeview">
  <li class="nav-item"><a href="https://support.baseserv.com/" target="_blank" class="nav-link"><span>{{ trans('backend.cb-billingservsupport') }}</span></a></li>
  <li class="nav-item"><a href="https://docs.billingserv.com/" target="_blank" class="nav-link"><span>{{ trans('backend.cb-billingservdocs') }}</span></a></li>
  <li class="nav-item"><a href="https://feedback.userreport.com/27890337-856a-4afe-8456-017bca6faf26/" onclick="event.preventDefault(); _urq.push(['Feedback_Open'])" class="nav-link"><span>{{ trans('backend.cb-billingservideas') }}</span></a></li>
</ul>
</li>
@endif
