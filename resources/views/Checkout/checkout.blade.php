@extends('Common.frontendLayout')
@section('title', 'Checkout')

@section('css')
<link rel="stylesheet" href="https://v2.b-cdn.uk/dist/css/checkout.css">
@stop

@section('content')
<div class="content">
<div class="center">

<div class="content-header">
        <h1>Checkout</h1>
</div><!-- content-header -->

					<div class="row">
                        <aside class="col-md-9">
                        <div id="accordion">
  <ul>
    <li>
      <a href="#one">Billing Information</a>
      <div id="one" class="accordion">
          <div class="row">
          <div class="col-lg-6">
  <label for="usr">Full Name:</label>
  <input type="text" class="form-control" id="usr">
          </div>
          <div class="col-lg-6">
  <label for="pwd">Address Line 1:</label>
  <input type="password" class="form-control" id="pwd">
          </div>
          <div class="col-lg-6">
  <label for="pwd">Address Line 2:</label>
  <input type="password" class="form-control" id="pwd">
          </div>
          <div class="col-lg-6">
  <label for="pwd">City:</label>
  <input type="password" class="form-control" id="pwd">
          </div>
          <div class="col-lg-6">
  <label for="pwd">County/State:</label>
  <input type="password" class="form-control" id="pwd">
          </div>
          <div class="col-lg-6">
  <label for="pwd">Postcode/Zip Code:</label>
  <input type="password" class="form-control" id="pwd">
          </div>
          <div class="col-lg-6">
  <label for="pwd">Billing Phone:</label>
  <input type="password" class="form-control" id="pwd">
          </div>
          <div class="col-lg-6">
  <label for="pwd">Email Address:</label>
  <input type="password" class="form-control" id="pwd">
          </div>
      </div>
        </div>
    </li>
    <li>
      <a href="#two">Review Payment</a>
      <div id="two" class="accordion">
          <div class="row">
              <div class="col-lg-12">
Please select the preferred payment method to use on this order.<br><br>
              </div>
              <div class="col-lg-12">
              <ul class="nav nav-tabs" id="myTab">
			  <li class="active"><a data-target="#home" data-toggle="tab">Can't think of a title!</a></li>
			  <li><a data-target="#cc-dc" data-toggle="tab">Pay with Credit/Debit Cards.</a></li>
			  <li><a data-target="#messages" data-toggle="tab">Pay with &nbsp;&nbsp;<img src="https://v2.b-cdn.uk/dist/img/pp-logo-200px.png" width="70px"></a></li>
			  <li><a data-target="#settings" data-toggle="tab">Settings</a></li>
			</ul>

			<div class="tab-content">
			  <div class="tab-pane active" id="home"></div>
			  <div class="tab-pane" id="cc-dc"><br>
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="https://v2.b-cdn.uk/dist/img/credit_card_logos.png" width="300px"><br><br>
                  <div class="col-lg-6">
  <label for="usr">Card Number:</label>
  <input type="text" class="form-control" id="usr">
          </div>
          <div class="col-lg-6">
  <label for="pwd">Name on Card:</label>
  <input type="password" class="form-control" id="pwd">
          </div>
          <div class="col-lg-6">
  <label for="pwd">Expiry Date:</label>
  <input type="password" class="form-control" id="pwd">
          </div>
          <div class="col-lg-6">
  <label for="pwd">CVV Security Number:</label>
  <input type="password" class="form-control" id="pwd">
          </div></div>
			  <div class="tab-pane" id="messages"></div>
			  <div class="tab-pane" id="settings"></div>
			</div>

</div>
        </div>
        </div>
    </li>
    <li>
      <a href="#three">Confirm Order</a>
      <div id="three" class="accordion">
          <div class="row">
          <div class="col-md-10 col-md-offset-1">
        <div class="form-group">
  <label for="comment">Additional Notes:</label>
  <textarea class="form-control" rows="5" id="comment"></textarea>
</div>
              <div class="alert alert-success" role="alert">This order form is provided in a secure environment and to help protect against fraud your current IP address (141.101.70.247) is being logged.</div>
          </div>
              <div class="float-right">
                  <div class="col-md-6">
              <button type="button" class="btn btn-success btn-lg">Complete Order</button>
                  </div>
              </div>
          </div>
      </div>
    </li>
  </ul>
</div>
                        </aside>
<aside class="total-sidebar col-md-3 col-xs-12">
							<!--
								This will be where we keep track of the checkout step and possibly offer extra navigation
							-->
							<ul>
								<li class="hitem">
									Your Order
								</li>
                                <li class="total clearfix">
									<div class="float-left lab">
										Home Package<br>
                                        Shared Hosting
									</div>
									<div class="float-right">
										<span class="currsym">&pound;</span>
										<span class="amount">7.50</span>
										<span class="curr3">GBP</span>
									</div>
								</li>
                                <li class="subtotal clearfix">
									<div class="float-left lab">
										Setup Fees
									</div>
									<div class="float-right">
										<span class="currsym">&pound;</span>
										<span class="amount">0.00</span>
										<span class="curr3">GBP</span>
									</div>
								</li>
								<li class="subtotal clearfix">
									<div class="float-left lab">
										Subtotal
									</div>
									<div class="float-right">
										<span class="currsym">&pound;</span>
										<span class="amount">7.50</span>
										<span class="curr3">GBP</span>
									</div>
								</li>
								<li class="total clearfix">
									<div class="float-left lab">
										Total
									</div>
									<div class="float-right">
										<span class="currsym">&pound;</span>
										<span class="amount">7.50</span>
										<span class="curr3">GBP</span>
									</div>
								</li><br>
                    <div class="form-group">
                        <label for="discount-code">Discount Code:</label>
                        <input type="text" class="form-control" id="discount-code">
                        <br>
                        <button type="button" class="btn btn-success btn-block">Checkout</button>
                    </div>
						</aside>

					</div>
          </div></div>
@stop
