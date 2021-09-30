@extends ('Common.template')

@section('title', ' Marketing')

@section('page.title', 'Marketing')
@section('page.subtitle', 'Marketing')

@section('breadcrumbs')
	<li>Marketing</li>
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
<div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Fixed Discounts</h3><br><br>
<p>The fixed discount gives a percentage based on the total amount of the order. If this amount is set to £100.00 and the percentage is 10%, customers who place an order for at least or more than £100.00 will receive a 10% discount on their order.</p>
                <a href="/marketing/fixed-discounts/" class="btn btn-success" role="button">Setup Fixed Discounts</a>
              </div>
            </div>
    </div>
    <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Discount Codes</h3><br><br>
<p>Discounts allow you to give discount codes to individuals or groups to use when placing an order. Towards the end of the checkout process, if a discount code is supplied, a percentage discount will be applied to the order.</p>
                <a href="/marketing/discount-codes/" class="btn btn-success" role="button">Setup Discount Codes</a>
              </div>
            </div>
    </div>
</div>
@stop
