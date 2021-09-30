@extends ('Common::template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Integrations')

@section('breadcrumbs')
	<li>Settings</li>
	<li><a href="/settings/integrations">Integrations</a></li>
	<li class="active">NetEarth One</li>
@stop

@section('content')
		<div class="row">
			<div class="col-sm-6">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">NetEarth One Information</h3>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="name">Enable Test Mode: </label>
							<input type="checkbox" name="name" id="name" class="form-control" value="" required>
						</div>
					</div>
                    <div class="box-body">
						<div class="form-group">
							<label for="name">User ID*: </label>
							<input type="text" name="name" id="name" class="form-control" value="" required>
						</div>
					</div>
                    <div class="box-body">
						<div class="form-group">
							<label for="name">API Key*: </label>
							<input type="text" name="name" id="name" class="form-control" value="" required>
						</div>
					</div>
                    <div class="box-body">
						<div class="form-group">
							<label for="name">Default NS 1*: </label>
							<input type="text" name="name" id="name" class="form-control" value="" required>
						</div>
					</div>
                    <div class="box-body">
						<div class="form-group">
							<label for="name">Default NS 2*: </label>
							<input type="text" name="name" id="name" class="form-control" value="" required>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">NetEarth One Import</h3><br><br>

                        <a href="/settings/hc/directadmin/import"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Import Customers</button></a>
					</div>

					</div>
				</div>
			</div>

		<a href="/settings/integrations"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
		<button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"> Update Settings</i></button>
	</form>
@stop