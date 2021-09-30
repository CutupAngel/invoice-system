@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Payment Gateways')

@section('breadcrumbs')
	<a href="/settings/paymentgateways">Payment Gateways</a>
	<li class="breadcrumb-item active">Cardinity</li>
@stop

@section('content')

@if (count($errors) > 0)
	<div class="alert alert-danger">
		<ul>
			@foreach ($errors->all() as $error)
				<li>{{$error}}</li>
			@endforeach
		</ul>
	</div>
@endif

@if (session('status'))
		<div class="alert alert-success">
				{{ session('status') }}
		</div>
@endif

<form method="post">

	<input type="hidden" name="_token" value="{{ csrf_token() }}">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Cardinity Information</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="consumer_key">Consumer Key: </label>
							<input type="text" name="consumer_key" id="consumer_key" class="form-control" value="{{ old('consumer_key', Settings::get('cardinity.consumer_key')) }}">
						</div>
						<div class="form-group">
							<label for="consumer_secret">Consumer Secret: </label>
							<input type="text" name="consumer_secret" id="consumer_secret" class="form-control" value="{{ old('consumer_secret', Settings::get('cardinity.consumer_secret')) }}">
						</div>
						<div class="form-group">
							<label for="currency">Currency: </label>
							<select name="currency" id="currency" class="form-control" value="{{ old('currency', Settings::get('cardinity.currency')) }}">
								<option value="GBP" @php if(Settings::get('cardinity.currency') == 'GBP') echo 'selected'; @endphp>GBP</option>
								<option value="EUR" @php if(Settings::get('cardinity.currency') == 'EUR') echo 'selected'; @endphp>EUR</option>
								<option value="USD" @php if(Settings::get('cardinity.currency') == 'USD') echo 'selected'; @endphp>USD</option>
							</select>
						</div>
					</div>
					<div class="card-footer">
					<a href="/settings/paymentgateways"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
					<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"></i> Update Settings</button>
					<a href="/settings/paymentgateway/clear/cardinity/"><button type="button" class="btn btn-danger float-right mr-2"> <i class="fa fa-arrow-fa-remove"></i> Clear</button></a>
				</div>
				</div>
			</div>
		</div>

	</form>
@stop
