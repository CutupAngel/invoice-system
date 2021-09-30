@extends ('Common.template')

@section('title', 'Import/Export')

@section('page.title', 'Import/Export')

@section('breadcrumbs')
	<li class="active">{{ trans('backend.inex-welcome') }}</li>
@stop

@section('content')
	@if(session()->has('message'))
		<div class="alert alert-success">
			<button type="button" class="close" data-dismiss="alert">×</button>
			{{ session()->get('message') }}
		</div>
	@endif
	@if (count($errors) > 0)
		<div class="alert alert-dismissible alert-danger">
			<button type="button" class="close" data-dismiss="alert">×</button>
			@foreach ($errors->all() as $error)
				{{$error}}<br>
			@endforeach
		</div>
	@endif
	<form method="post" enctype="multipart/form-data">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">
		<div class="row">
			<div class="col-lg-6">
				<div class="card">
					<div class="card-header">
						{{ trans('backend.inex-importcust') }}
						(Max. Unlimited Customers)
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="format">{{ trans('backend.inex-format') }}</label>
  							<select class="form-control" id="sel1">
	    						<option>{{ trans('backend.inex-billingservcsv') }}</option>
  							</select>
						</div>
						<div class="form-group">
							<label class="control-label">{{ trans('backend.inex-importcust') }}</label>
							<input id="input-1" type="file" class="file" accept=".csv" name="file_customer">
						</div>
						<button
							class="btn btn-success float-left"
							name="import"
							value="customers"
						>{{ trans('backend.inex-import') }}</button>
					</div>
				</div>
				<div class="card card-default">
					<div class="card-header">
						{{ trans('backend.inex-importtrans') }}
						(Max. Unlimited Transaction)
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="format">{{ trans('backend.inex-format') }}</label>
  						<select class="form-control" id="sel1">
    					<option>{{ trans('backend.inex-billingservcsv') }}</option>
  						</select>
						</div>
						<div class="form-group">
							<label class="control-label">{{ trans('backend.inex-importtrans') }}</label>
							<input id="input-2" type="file" class="file" accept=".csv" name="file_transaction">
						</div>
						<button
							class="btn btn-success float-left"
							name="import"
							value="transactions"
						>{{ trans('backend.inex-import') }}</button>
					</div>
				</div>
				<div class="card">
					<div class="card-header">
						Import from another Billing Platform.
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="format">{{ trans('backend.inex-format') }}</label>
  							<select class="form-control" id="sql_type" name="sql_type">
	    						<option value="whmcs">WHMCS SQL</option>
								<option value="blesta">Blesta SQL</option>
								<!--<option value="clientexec">Clientexec SQL</option>-->
  							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Import SQL File</label>
							<input id="input-1" type="file" class="file" accept=".sql" name="file_billing">
						</div>
						<button
							class="btn btn-success float-left"
							name="import"
							value="billing"
						>{{ trans('backend.inex-import') }}</button>
					</div>
				</div>
				<a href="/settings/integrations"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> {{ trans('backend.inex-return') }}</button></a>
			</div>
			<div class="col-lg-6">
				<div class="card card-default">
					<div class="card-header">
						{{ trans('backend.inex-exportcust') }}
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="format">{{ trans('backend.inex-format') }}</label>
  						<select class="form-control" id="sel1">
    					<option>{{ trans('backend.inex-billingservcsv') }}</option>
  						</select>
						</div>
						<div class="form-group">
							<b>{{ trans('backend.inex-from') }}</b>
							<span>
								<input type="date" name="from" value="" id="duedate">
							</span><br>
							<b>{{ trans('backend.inex-to') }}</b>
							<span>
								<input type="date" name="to" value="" id="duedate">
							</span>
						</div>
						<button
							class="btn btn-success float-left"
							name="export"
							value="customers"
						>{{ trans('backend.inex-export') }}</button>
						<div class="col-md-12">
							@if (session()->has('customers-massage'))
								{{ session('customers-massage') }}
							@endif
						</div>
					</div>
				</div>
				<div class="card card-default">
					<div class="card-header">
						{{ trans('backend.inex-exporttrans') }}
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="format">{{ trans('backend.inex-format') }}</label>
							<select class="form-control" id="sel1">
							<option>{{ trans('backend.inex-billingservcsv') }}</option>
							</select>
						</div>
						<div class="form-group">

						<b>{{ trans('backend.inex-from') }}</b> <span><input type="date" name="from" value="" id="duedate"></span><br>
						<b>{{ trans('backend.inex-to') }}</b> <span>
						<input type="date" name="to" value="" id="duedate"></span>

						</div>
						<button	class="btn btn-success float-left" name="export" value="transactions">{{ trans('backend.inex-export') }}</button>
						<div class="col-md-12">
							@if (session()->has('transactions-massage'))
								{{ session('transactions-massage') }}
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
@stop
