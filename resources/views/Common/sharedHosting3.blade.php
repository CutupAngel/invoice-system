@extends('Common.frontendLayout')
@section('title', $packageData->name)

@section('css')
<link rel="stylesheet" href="https://v2.b-cdn.uk/dist/css/sharedHosting3.css">
@stop

@section('content')
					<div class="row">
						<section class="layer plans">
							<section>


								<section class="third lift plan-tier" onclick="location.href='#';">


									<h4>Solo</h4>
									<h5><sup class="superscript">$</sup><span class="plan-price">5</span><sub> /mo</sub></h5>


									<p class="early-adopter-price">Early adopter price</p><br>
									<del>$10/mo</del>


									<ul>
										<li><strong>1</strong> site</li>
										<li><strong>1</strong> user</li>
										<li><strong>Free hosting</strong> (beta)</li>
										<li>14 day <strong>free trial</strong></li>
									</ul>

								</section>

								<section class="third lift plan-tier callout" onclick="location.href='#';">

									<h6>Most popular</h6>
									<h4>Team</h4>
									<h5><sup class="superscript">$</sup><span class="plan-price">20</span><sub> /mo</sub></h5>


									<p class="early-adopter-price">Early adopter price</p><br>
									<del>$40/mo</del>


									<ul>
										<li>Up to <strong>10</strong> sites</li>
										<li>Up to <strong>10</strong> users per site</li>
										<li><strong>Free hosting</strong> (beta)</li>
										<li>14 day <strong>free trial</strong></li>
									</ul>

								</section>

								<section class="third lift plan-tier" onclick="location.href='#';">


									<h4>Agency</h4>
									<h5><sup class="superscript">$</sup><span class="plan-price">100</span><sub> /mo</sub></h5>


									<p class="early-adopter-price">Early adopter price</p><br>
									<del>$200/mo</del>


									<ul>
										<li><strong>Unlimited</strong> sites</li>
										<li><strong>Unlimited</strong> users</li>
										<li><strong>Free hosting</strong> (beta)</li>
										<li>14 day <strong>free trial</strong></li>
									</ul>

								</section>

								<div style="clear: both"></div>
							</section>
						</section>
					</div>
@stop

@section('js')

@stop
