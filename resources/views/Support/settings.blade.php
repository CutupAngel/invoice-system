@extends ('Common.template')

@section('title', 'Support Settings')

@section('page.title', 'Support Settings')

@section('breadcrumbs')
	<li class="active">Support Settings</li>
@stop

@section('content')
	@if(session()->has('success'))
		<div class="alert alert-success">
			{{ session()->get('success') }}
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
  <div class="row">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Support Desk Settings</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
                        <label>Disable Support Tickets?</label>
                        <select class="form-control">
                          <option>Yes</option>
                          <option>No</option>
                        </select>
            </div>
            <div class="form-group">
                          <label>Disable Pre-Sales Form?</label>
                          <select class="form-control">
                            <option>Yes</option>
                            <option>No</option>
                          </select>
              </div>
        </div>
        <div class="card-footer">
        <button type="submit" class="btn btn-success float-right">{{ trans('backend.inv-save') }}</button>
      </div>
      </div>
    </div>
  </div>
  @stop
