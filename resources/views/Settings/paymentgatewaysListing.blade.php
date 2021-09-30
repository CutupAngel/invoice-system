@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Payment Gateways')

@section('breadcrumbs')
	<li class="active">Payment Gateways</li>
@stop

@section('content')
  <div class="card-body">
		@if (count($errors) > 0)
			<div class="alert alert-dismissible alert-danger">
				<button type="button" class="close" data-dismiss="alert">×</button>
				@foreach ($errors->all() as $error)
					{{$error}}<br>
				@endforeach
			</div>
		@endif
	</div>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Onsite Gateways<small> Only one can be enabled.</small></h3>
    </div>
    <!-- /.box-header -->
    <div class="card-body p-0 table-responsive">
      <table class="table table-striped">
        <tr>
          <th>Name</th>
          <th>Description</th>
          <th>Enabled</th>
          <th>Update</th>
        </tr>
        <tr>
        <!--<tr>
          <td>Authorize.net</td>
          <td>Payment gateway enables internet merchants to accept online payments via credit card and e-check.</td>
          <td>{{ ($default == 'authorize') ? "Enabled" : "Disabled" }}</td>
          <td><a href="/settings/paymentgateways/authorize" class="float-right"><button class="btn btn-success">Update</button></a></td>
        </tr>-->
        <tr>
          <td>BluePay</td>
          <td>BluePay is the one-stop shop for all your payment processing solutions. We serve B2B, B2G, and enterprise, small and medium-sized businesses.</td>
          <td>{{ $default === 'bluepay' ? "Enabled" : "Disabled" }}</td>
          <td><a href="/settings/paymentgateways/bluepay" class="float-right"><button class="btn btn-success">Update</button></a></td>
        </tr>
        <tr>
          <td>GoCardless Pro</td>
          <td>GoCardless.</td>
          <td>{{ $default === 'gocardless' ? "Enabled" : "Disabled" }}</td>
          <td><a href="/settings/paymentgateways/gocardless" class="float-right"><button class="btn btn-success">Update</button></a></td>
        </tr>
        <tr>
          <td>Merchant Focus</td>
          <td>We've partnered with Merchant Focus and BluePay to bring you fantasic rates for high risk merchants, if you have been asked to signup for a Merchant Focus account you can do so here.</td>
          <td>N/A</td>
          <td><a href="/settings/paymentgateways/merchantfocus"><button class="btn btn-success float-right">Signup</button></a></td>
        </tr>
          <td>PayPal Pro</td>
          <td>PayPal Payments Pro is an affordable website payment processing solution for businesses with 100+ orders/month.</td>
          <td>{{ $default === 'paypalpro' ? "Enabled" : "Disabled" }}</td>
          <td><a href="/settings/paymentgateways/paypalpro" class="float-right"><button class="btn btn-success">Update</button></a></td>
        </tr>
        <tr>
          <td>Stripe</td>
          <td>Web and mobile payments, built for developers. A set of unified APIs and tools that instantly enables businesses to accept and manage online payments.</td>
          <td>{{ $default === 'stripe' ? "Enabled" : "Disabled" }}</td>
          <td><a href="/settings/paymentgateways/stripe" class="float-right"><button class="btn btn-success">Update</button></a></td>
        </tr>
        <tr>
          <td>WorldPay</td>
          <td>Worldpay provides secure payment services for small and large businesses, including payments online, card machines and telephone payments.</td>
          <td>{{ $default === 'worldpay' ? "Enabled" : "Disabled" }}</td>
          <td><a href="/settings/paymentgateways/worldpay" class="float-right"><button class="btn btn-success">Update</button></a></td>
        </tr>
				<tr>
          <td>Cardinity</td>
          <td>Cardinity enables merchants to accept credit and debit cards from buyers worldwide. Increase your sales volume and profit with the all-in-one Cardinity payment system.</td>
          <td>{{ $default === 'cardinity' ? "Enabled" : "Disabled" }}</td>
          <td><a href="/settings/paymentgateways/cardinity" class="float-right"><button class="btn btn-success">Update</button></a></td>
        </tr>
				<tr>
          <td>Bank Transfer</td>
          <td>Bank Transfers, enter your bank details into the form provided and these will be shown on the checkout.</td>
          <td>{{ $default === 'banktransfer' ? "Enabled" : "Disabled" }}</td>
          <td><a href="/settings/paymentgateways/banktransfer" class="float-right"><button class="btn btn-success">Update</button></a></td>
        </tr>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Offsite Gateways</h3>
    </div>
    <!-- /.box-header -->
    <div class="card-body p-0 table-responsive">
      <table class="table table-striped">
        <tr>
          <th>Name</th>
          <th>Description</th>
          <th>Enabled</th>
          <th>Update</th>
        </tr>
        <tr>
          <td>PayPal Standard</td>
          <td>Discover PayPal, the safer way to pay, receive payments for your goods or services and transfer money to friends and family online.</td>
          <td>{{ !empty($gateways['paypalstandard']) ? "Enabled" : "Disabled" }}</td>
          <td><a href="/settings/paymentgateways/paypalstandard" class="float-right"><button class="btn btn-success">Update</button></a></td>
        </tr>
        <tr>
          <td>PYMT Pro</td>
          <td>Accept digital currency.</td>
          <td>{{ !empty($gateways['pymtpro']) ? "Enabled" : "Disabled" }}</td>
          <td><a href="/settings/paymentgateways/pymtpro" class="float-right"><button class="btn btn-success">Update</button></a></td>
        </tr>
        <!--<tr>
          <td>WorldPay</td>
          <td>Worldpay provides secure payment services for small and large businesses, including payments online, card machines and telephone payments.</td>
          <td>{{ !empty($gateways['worldpay']) ? "Enabled" : "Disabled" }}</td>
          <td><a href="/settings/paymentgateways/worldpay" class="float-right"><button class="btn btn-success">Update</button></a></td>
        </tr>
        <tr>
          <td>2Checkout</td>
          <td>Accept payments online. 2Checkout.com is an online payment processing service that helps you accept credit cards, PayPal and debit cards.</td>
          <td>{{ !empty($gateways['2checkout']) ? "Enabled" : "Disabled" }}</td>
          <td><a href="/settings/paymentgateways/2checkout" class="float-right"><button class="btn btn-success">Update</button></a></td>
        </tr>-->
      </table>
    </div>
  </div>
@stop

@section('css')
  <style>
    img.alignright { float: right; margin: 0 0 1em 1em; }
    img.alignleft { float: left; margin: 0 1em 1em 0; }
    img.aligncenter { display: block; margin-left: auto; margin-right: auto; }
    .alignright { float: right; }
    .alignleft { float: left; }
    .aligncenter { display: block; margin-left: auto; margin-right: auto; }
    .bluepay-css {padding: 30px 10px 15px 10px; background-color: white; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px;}
    .bluepay-signup {width: 250px; float:right;}
    .twoco-css {padding: 30px 10px 15px 10px; background-color: white; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px;}
    .twoco-signup {width: 250px; float:right;}
    .bluepay-text-left {float: left; width:450px;}
    .bluepay-signup-form-right {float: right; width:400px;}
    .modal-footer {padding: 15px; text-align: right;}
    .modal-footer { border-top-color: #FFF; }
    .price {
      font-size: 4em;
    }

    .price-cents {
      vertical-align: super;
      font-size: 50%;
    }

    .price-month {
      font-size: 35%;
      font-style: italic;
    }
    .panel {
      -webkit-transition-property : scale;
      -webkit-transition-duration : 0.2s;
      -webkit-transition-timing-function : ease-in-out;
      -moz-transition : all 0.2s ease-in-out;
    }

    .panel:hover {
      box-shadow: 0 0 10px rgba(0,0,0,.5);
      -moz-box-shadow: 0 0 10px rgba(0,0,0,.5);
      -webkit-box-shadow: 0 0 10px rgba(0,0,0,.5);
      -webkit-transform: scale(1.05);
      -moz-transform: scale(1.05);
    }
  </style>
@stop

@section('javascript')
  <script>
    (function($) {
      $('#merchant-form').on('submit', function() {
        $.post('/settings/paymentgateways/merchantfocus', $(this).serialize(), function(data, textStatus, xhr) {
          console.log(data);
        });

        return false;
      });
    }(jQuery));
  </script>
@stop
