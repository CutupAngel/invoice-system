@extends('Common.frontendLayout')
@section('title', 'Viewcart')
@section('content')
<div class="jumbotron">
	<div class="container">

		<div class="content-header">
			View Cart
		</div><!-- content-header -->

        <div class="col-sm-12 col-md-10 col-md-offset-1 table-responsive" id="ajax-cart">
        	@include('Common.cart.cart')
        </div>
				<tr>
					<td colspan="5">
						<a href="/checkout">
							<button type="button" class="btn btn-success pull-right" id="frmCheckout">{{ trans('frontend.vc-checkout') }}</button>
						</a>
					</td>
				</tr>
	</div><!-- center -->
</div>
@stop
