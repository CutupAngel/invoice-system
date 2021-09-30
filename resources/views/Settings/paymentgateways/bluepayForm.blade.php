@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Payment Gateways')

@section('breadcrumbs')
	<a href="/settings/paymentgateways">Payment Gateways</a>
	<li class="breadcrumb-item active">Bluepay</li>
@stop

@section('content')
	<form method="post">
		<input type="hidden" name="_token" value="{{csrf_token()}}">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Bluepay Information</h3>
					</div>
					<div class="card-body">
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
								{{session('status')}}
							</div>
						@endif

						<div class="form-group">
							<label for="account_id">Account ID: </label>
							<input type="text" name="account_id" id="account_id" class="form-control" value="{{old('account_id', Settings::get('bluepay.account_id'))}}" required>
						</div>

						<div class="form-group">
							<label for="secretkey">Secret Key: </label>
							<input type="text" name="secretkey" id="secretkey" class="form-control" value="{{old('secretkey', Settings::get('bluepay.secretkey'))}}" required>
						</div>

						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="testmode" {{ empty(old('testmode', Settings::get('bluepay.testmode'))) ? '' : 'checked' }} value="1">
									Test Mode
								</label>
							</div>
						</div>
					</div>
					<div class="card-footer">
					<a href="/settings/paymentgateways"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
					<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"></i> Update Settings</button>
					<a href="/settings/paymentgateway/clear/bluepay/"><button type="button" class="btn btn-danger float-right mr-2"> <i class="fa fa-arrow-fa-remove"></i> Clear</button></a>
				</div>
				</div>
			</div>
		</div>

	</form>
@stop
