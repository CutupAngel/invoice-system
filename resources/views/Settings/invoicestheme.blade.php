@extends ('Common::template')

@section('title', ' Theme Settings')

@section('page.title', 'Theme Settings')
@section('page.subtitle', 'Invoice Customisations')

@section('breadcrumbs')
	<li>Theme Settings</li>
	<li class="active">Invoice Customisations</li>
@stop

@section('content')
<div class="box">
		<div class="box-body">
            <div class="col-lg-12">
            <div class="form-group">
  <label for="comment">Invoice Customisations:</label>
  <textarea class="form-control" rows="20" id="comment"></textarea>
</div>
            </div>
		</div>
		<div class="box-footer">
			<button class="btn btn-success float-right" data-toggle="modal" data-target="#Add-Tax-Rate"><i class="fa fa-plus"></i> Save Customisations</button>
		</div>
	</div>
@stop