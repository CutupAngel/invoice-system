{{-- Client Navigation --}}
@if (! Auth::User()->isCustomer())
  <li class="nav-item">
    <a href="/" class="nav-link">
      <i class="nav-icon fas fa-tachometer-alt"></i>
      <p>
        {{ trans('backend.cb-dashboard') }}
      </p>
    </a>
  </li>

  @if (Permissions::has('packages'))
  <li class="nav-item has-treeview">
    <a href="#" class="nav-link">
      <i class="nav-icon fa fa-list"></i>
      <p>
        {{ trans('backend.cb-packages') }}
        <i class="right fa fa-angle-left"></i>
      </p>
    </a>
    <ul class="nav nav-treeview">
      <li class="nav-item">
        <a href="/orders" class="nav-link">
          <p>{{ trans('backend.cb-packages') }}</p>
        </a>
      </li>
      <li class="nav-item">
        <a href="/orders/options" class="nav-link">
          <p>{{ trans('backend.cb-packagesoptions') }}</p>
        </a>
      </li>
    </ul>
  </li>
  @endif

  @if (Permissions::has('customers'))
    <li class="nav-item has-treeview">
      <a href="#" class="nav-link">
        <i class="nav-icon fa fa-users"></i>
        <p>
          {{ trans('backend.cb-customers') }}
          <i class="right fa fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="/customers" class="nav-link">
            <p>{{ trans('backend.cb-customersall') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/customers/create" class="nav-link">
            <p>{{ trans('backend.cb-customersnew') }}</p>
          </a>
        </li>
      </ul>
    </li>
  @endif

  @if (Permissions::has('invoices'))
    <li class="nav-item has-treeview">
      <a href="#" class="nav-link">
        <i class="nav-icon fas fa-file-invoice-dollar"></i>
        <p>
          {{ trans('backend.cb-invoices') }}
          <i class="right fa fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="/admin/invoices/view" class="nav-link">
            <p>{{ trans('backend.cb-invoicesall') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/admin/invoices/view/unpaid" class="nav-link">
            <p>{{ trans('backend.cb-invoicesunpaid') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/admin/invoices/view/overdue" class="nav-link">
            <p>{{ trans('backend.cb-invoicesoverdue') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/admin/invoices/view/paid" class="nav-link">
            <p>{{ trans('backend.cb-invoicespaid') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/admin/invoices/view/refunded" class="nav-link">
            <p>{{ trans('backend.cb-invoicesrefunded') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/admin/invoices/view/canceled" class="nav-link">
            <p>{{ trans('backend.cb-invoicescanceled') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/admin/invoices/create" class="nav-link">
            <p>{{ trans('backend.cb-invoicescreate') }}</p>
          </a>
        </li>
      </ul>
    </li>
  @endif

  @if (Permissions::has('marketing'))
    <li class="nav-item has-treeview">
      <a href="#" class="nav-link">
        <i class="nav-icon fas fa-ad"></i>
        <p>
          {{ trans('backend.cb-marketing') }}
          <i class="right fa fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="/marketing" class="nav-link">
            <p>{{ trans('backend.cb-marketingpromos') }}</p>
          </a>
        </li>
      </ul>
    </li>
  @endif

  @if (Permissions::has('reports'))
    <li class="nav-item has-treeview">
      <a href="#" class="nav-link">
        <i class="nav-icon fa fa-book"></i>
        <p>
          {{ trans('backend.cb-reports') }}
          <i class="right fa fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="/reports/annual-sales" class="nav-link">
            <p>{{ trans('backend.cb-reportsannual') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/reports/sales-by-staff" class="nav-link">
            <p>{{ trans('backend.cb-reportssalesstaff') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/reports/sales-by-customers" class="nav-link">
            <p>{{ trans('backend.cb-reportssalescustomer') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/reports/loginhistory" class="nav-link">
            <p>{{ trans('backend.cb-reportslogin') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/reports/revenue-trend" class="nav-link">
            <p>{{ trans('backend.cb-reportsrevenue') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/reports/package-leaderboard" class="nav-link">
            <p>{{ trans('backend.cb-reportspackage') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/reports/customer-invoice-report" class="nav-link">
            <p>{{ trans('backend.cb-reportsinvoicecustomer') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/reports/customer-credit-report" class="nav-link">
            <p>{{ trans('backend.cb-reportscustomercredit') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/reports/customer-receipts-report" class="nav-link">
            <p>{{ trans('backend.cb-reportscustomerreceipts') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/reports/debt-sheet" class="nav-link">
            <p>{{ trans('backend.cb-reportsdebt') }}</p>
          </a>
        </li>
      </ul>
    </li>
  @endif

  @if (Permissions::has('support'))
    <li class="nav-item has-treeview">
      <a href="#" class="nav-link">
        <i class="nav-icon fa fa-life-ring"></i>
        <p>
          {{ trans('backend.cb-supportsecond') }}
          <i class="right fa fa-angle-left"></i>
        </p>
        </a>
      <ul class="nav nav-treeview">
        <!-- <li class="nav-item">
          <a href="/support/dashboard" class="nav-link">
            {{ trans('backend.cb-supportdash') }}
          </a>
        </li>-->
        <li class="nav-item">
          <a href="/support/tickets" class="nav-link">
            {{ trans('backend.cb-supporttickets') }}
          </a>
        </li>
        {{--<li class="nav-item">--}}
          {{--<a href="/support/settings" class="nav-link">--}}
            {{--{{ trans('backend.cb-supportsettings') }}--}}
          {{--</a>--}}
        {{--</li>--}}
      </ul>
    </li>
  @endif

  @if (Permissions::has('settings'))
    <li class="nav-item has-treeview">
      <a href="#" class="nav-link">
        <i class="nav-icon fas fa-user-cog"></i>
        <p>
          {{ trans('backend.cb-settings') }}
          <i class="right fa fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="/settings/my-account" class="nav-link">
            <p>{{ trans('backend.cb-settingsmya') }}</p>
          </a>
        </li>
        @if (Auth::User()->isClient() || Auth::User()->isAdmin())
        <li class="nav-item">
          <a href="/settings/staff" class="nav-link">
            <p>{{ trans('backend.cb-settingsstaff') }}</p>
          </a>
        </li>
        @endif
        <li class="nav-item">
          <a href="/settings/invoice-settings" class="nav-link">
            <p>{{ trans('backend.cb-settingsinvoice') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/settings/invoice-settings/tax-zones" class="nav-link">
            <p>{{ trans('backend.cb-settingstax') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/settings/paymentgateways" class="nav-link">
            <p>{{ trans('backend.cb-settingspayment') }}</p>
          </a>
        </li>
		@if (Config('app.site')->activeSubscription && Config('app.site')->activeSubscription->plan_id != '1')
        <li class="nav-item">
          <a href="/settings/design-settings" class="nav-link">
            <p>{{ trans('backend.cb-settingsdesgin') }}</p>
          </a>
        </li>
        @endif
        <!--<li><a href="#">{{ trans('backend.cb-settingsdomain') }}</a></li>-->
        <li class="nav-item">
          <a href="/settings/integrations" class="nav-link">
            <p>{{ trans('backend.cb-settingsigt') }}</p>
          </a>
        </li>
      </ul>
    </li>
  @endif
@endif

@if (Config('app.site')->super)
<li class="nav-item has-treeview {{ $current('/plans') }}">
  <a href="#"><i class="nav-icon fa fa-list"></i> <span>BillingServ Plans</span><i class="fa fa-angle-left float-right"></i></a>
  <ul class="nav nav-treeview">
    <li class="{{ $current('plans') }}"><a href="/plans" class="nav-link">Plans</a></li>
  </ul>
</li>
@endif

{{-- Customer Navigation --}}
@if (Auth::User()->isCustomer())
  <li class="nav-item">
    <a href="/" class="nav-link">
      <i class="nav-icon fas fa-tachometer-alt"></i>
      <p>
        {{ trans('backend.cb-dashboard') }}
      </p>
    </a>
  </li>

  <!--<li class="treeview">
    <a href="#"><i class="fa fa-list"></i> <span>{{ trans('backend.cd-services') }}</span> <i class="fa fa-angle-left float-right"></i></a>
    <ul class="treeview-menu">
      <li><a href="#">{{ trans('backend.cd-servicesmy') }}</a></li>
      <li><a></a></li>
      <li><a href="#">{{ trans('backend.cd-servicescreate') }}</a></li>
    </ul>
  </li>-->
  @if($PackageClass::where('domainIntegration',1)->count() && 1 === 0)
  <li class="nav-item has-treeview">
    <a href="#"><i class="fa fa-external-link"></i> <span>{{ trans('backend.cd-domains') }}</span> <i class="fa fa-angle-left float-right"></i></a>
    <ul class="treeview-menu">
      <li><a href="#">{{ trans('backend.cd-domainsmy') }}</a></li>
      <li><a href="#">{{ trans('backend.cd-domainsrenew') }}</a></li>
      <li><a href="#">{{ trans('backend.cd-domainstrans') }}</a></li>
      <li><a href="#">{{ trans('backend.cd-domainsorder') }}</a></li>
    </ul>
  </li>
  @endif
<li class="nav-item has-treeview">
  <a href="#" class="nav-link">
    <i class="nav-icon fas fa-file-invoice-dollar"></i>
    <p>
      {{ trans('backend.cd-billing') }}
      <i class="right fa fa-angle-left"></i>
    </p>
  </a>
  <ul class="nav nav-treeview">
    <li class="nav-item">
      <a href="/invoices" class="nav-link">
        <p>{{ trans('backend.cd-billinginvoices') }}</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/invoices/view/estimates" class="nav-link">
        <p>{{ trans('backend.cd-billingquotes') }}</p>
      </a>
    </li>
    </ul>
  </li>
    <li class="nav-item">
      <a href="/products-ordered" class="nav-link">
        <i class="nav-icon fas fa-cogs"></i>
        <p>
          {{ trans('backend.cb-services') }}
        </p>
      </a>
    </li>
    <li class="nav-item">
      <a href="/settings/myaccount" class="nav-link">
        <i class="nav-icon fa fa-users"></i>
        <p>
          {{ trans('backend.cb-settings') }}
        </p>
      </a>
    </li>

      <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
          <i class="nav-icon fa fa-life-ring"></i>
          <p>
            {{ trans('backend.cb-supportsecond') }}
            <i class="right fa fa-angle-left"></i>
          </p>
          </a>
        <ul class="nav nav-treeview">
          <!-- <li class="nav-item">
            <a href="/support/dashboard" class="nav-link">
              {{ trans('backend.cb-supportdash') }}
            </a>
          </li>-->
          <li class="nav-item">
            <a href="/support/tickets" class="nav-link">
              {{ trans('backend.cb-supporttickets') }}
            </a>
          </li>
        </ul>
      </li>

  <!--<li class="treeview">
    <a href="#"><i class="fa fa-support"></i> <span>{{ trans('backend.cd-support') }}</span> <i class="fa fa-angle-left float-right"></i></a>
    <ul class="treeview-menu">
      <li><a href="#">{{ trans('backend.cd-supportview') }}</a></li>
      <li><a href="#">{{ trans('backend.cd-supportcreate') }}</a></li>
      <li><a href="#">{{ trans('backend.cd-supportann') }}</a></li>
      <li><a href="#">{{ trans('backend.cd-supportknow') }}</a></li>
      <li><a href="#">{{ trans('backend.cd-supportstatus') }}</a></li>
    </ul>
  </li>-->
@endif
