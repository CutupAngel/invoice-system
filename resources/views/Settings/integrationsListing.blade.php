@extends ('Common.template')

@section('title', ' Integrations')

@section('page.title', 'Settings')
@section('page.subtitle', 'Integrations')

@section('breadcrumbs')
	<li class="active">Integrations</li>
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

  @foreach ($types as $type)
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><a name="{{$type['title']}}">{{$type['title']}}</a></h3>
      </div>
      <div class="card-body p-0 table-responsive">
        <table class="table table-stripe">
          <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Status</th>
            <th>Update</th>
          </tr>
          @foreach ($type['integrations'] as $intergration)
            <tr data-integration="{{$integrations[$intergration]['shortname']}}">
              <td>{{$integrations[$intergration]['title']}}</td>
              <td>{{$integrations[$intergration]['description']}}</td>
              <td>
                @if ($integrations[$intergration]['status'])
                  <button type="button" class="btn btn-success status">Enabled</button>
                @else
                  <button type="button" class="btn btn-danger status">Disabled</button>
                @endif
              </td>
			   @if($intergration === 'importexport')
              <td><a href="/settings/integrations/{{$integrations[$intergration]['shortname']}}"><button class="btn btn-success">Import</button></a></td>
			   @else
              <td><a href="/settings/integrations/{{$integrations[$intergration]['shortname']}}"><button class="btn btn-success">Update</button></a></td>
			   @endif
            </tr>
          @endforeach
        </table>
      </div>
    </div>
  @endforeach
@stop

@section ('javascript')
  <script>
    $('.status').on('click', function() {
      var self = this;
      var integration = $(this).parents('tr').data('integration');
      $.ajax({
        url: '/settings/integrations',
        type: 'PUT',
        dataType: 'JSON',
        data: {'integration': integration},
      })
      .done(function(data) {
        console.log("data");
        if (data === 1) {
          $(self).removeClass('btn-danger').addClass('btn-success').text('Enabled');
        } else {
          if (data !== 0) {
            alert(data);
          }

          $(self).removeClass('btn-success').addClass('btn-danger').text('Disabled');
        }
      });
    });
  </script>
@stop
