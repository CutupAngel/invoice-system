@extends('Common.frontendLayout')
@section('title', $packageData->name)

@section('css')
<link rel="stylesheet" href="https://v2.b-cdn.uk/dist/css/sharedHosting2.css">
@stop

@section('content')
					<div class="row">
						<br><br><br><br>
						<section id="pricePlans">
							<ul id="plans">
								<li class="plan">
									<ul class="planContainer">
										<li class="title"><h2>Plan 1</h2></li>
										<li class="price"><p>$10/<span>month</span></p></li>
										<li>
											<ul class="options">
												<li>2x <span>option 1</span></li>
												<li>Free <span>option 2</span></li>
												<li>Unlimited <span>option 3</span></li>
												<li>Unlimited <span>option 4</span></li>
												<li>1x <span>option 5</span></li>
											</ul>
										</li>
										<li class="button"><a href="#">Purchase</a></li>
									</ul>
								</li>

								<li class="plan">
									<ul class="planContainer">
										<li class="title"><h2 class="bestPlanTitle">Plan 2</h2></li>
										<li class="price"><p class="bestPlanPrice">$20/month</p></li>
										<li>
											<ul class="options">
												<li>2x <span>option 1</span></li>
												<li>Free <span>option 2</span></li>
												<li>Unlimited <span>option 3</span></li>
												<li>Unlimited <span>option 4</span></li>
												<li>1x <span>option 5</span></li>
											</ul>
										</li>
										<li class="button"><a class="bestPlanButton" href="#">Purchase</a></li>
									</ul>
								</li>

								<li class="plan">
									<ul class="planContainer">
										<li class="title"><h2>Plan 3</h2></li>
										<li class="price"><p>$30/<span>month</span></p></li>
										<li>
											<ul class="options">
												<li>2x <span>option 1</span></li>
												<li>Free <span>option 2</span></li>
												<li>Unlimited <span>option 3</span></li>
												<li>Unlimited <span>option 4</span></li>
												<li>1x <span>option 5</span></li>
											</ul>
										</li>
										<li class="button"><a href="#">Purchase</a></li>
									</ul>
								</li>

								<li class="plan">
									<ul class="planContainer">
										<li class="title"><h2>Plan 4</h2></li>
										<li class="price"><p>$40/<span>month</span></p></li>
										<li>
											<ul class="options">
												<li>2x <span>option 1</span></li>
												<li>Free <span>option 2</span></li>
												<li>Unlimited <span>option 3</span></li>
												<li>Unlimited <span>option 4</span></li>
												<li>1x <span>option 5</span></li>
											</ul>
										</li>
										<li class="button"><a href="#">Purchase</a></li>
									</ul>
								</li>
							</ul> <!-- End ul#plans -->
						</section>

					</div>
@stop

@section('js')

@stop
