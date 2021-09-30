@extends('Common.frontendLayout')
@section('title', $packageData->name)

@section('css')
<link rel="stylesheet" href="https://v2.b-cdn.uk/dist/css/sharedHosting1.css">
@stop

@section('content')
					<div class="row">
						<div class="page-container">
							<div class="pricing-table">
								<div class="pricing-table-header">
									<h2>Personal Site</h2>
									<h3>$15/month</h3>
								</div>
								<div class="pricing-table-space"></div>
								<div class="pricing-table-features">
									<p><strong>50</strong> Email Addresses</p>
									<p><strong>35GB</strong> of Storage</p>
									<p><strong>40</strong> Databases</p>
									<p><strong>10</strong> Domains</p>
									<p><strong>24/7 Unlimited</strong> Support</p>
								</div>
								<div class="pricing-table-sign-up">
								  <p><a href="http://bloggingbolt.blogspot.com">Sign Up Now</a></p>
								</div>
							</div>

							<div class="pricing-table pricing-table-highlighted">
								<div class="pricing-table-header">
									<h2>Small Business</h2>
									<h3>$59/month</h3>
								</div>
								<div class="pricing-table-space"></div>
								<div class="pricing-table-text">
									<p><strong>This is a perfect choice for small businesses and startups.</strong></p>
								</div>
								<div class="pricing-table-features">
									<p><strong>Unlimited</strong> Email Addresses</p>
									<p><strong>65GB</strong> of Storage</p>
									<p><strong>75</strong> Databases</p>
									<p><strong>25</strong> Domains</p>
									<p><strong>24/7 Unlimited</strong> Support</p>
								</div>
								<div class="pricing-table-sign-up">
									<p><a href="http://bloggingbolt.blogspot.com">Sign Up Now</a></p>
								</div>
							</div>

							<div class="pricing-table">
								<div class="pricing-table-header">
									<h2>Corporate Site</h2>
									<h3>$85/month</h3>
								</div>
								<div class="pricing-table-space"></div>
								<div class="pricing-table-features">
									<p><strong>Unlimited</strong> Email Addresses</p>
									<p><strong>85GB</strong> of Storage</p>
									<p><strong>Unlimited</strong> Databases</p>
									<p><strong>50</strong> Domains</p>
									<p><strong>24/7 Unlimited</strong> Support</p>
								</div>
								<div class="pricing-table-sign-up">
									<p><a href="http://bloggingbolt.blogspot.com">Sign Up Now</a></p>
								</div>
							</div>
						</div>
					</div>
@stop

@section('js')

@stop
