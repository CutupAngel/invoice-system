@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Integrations')

@section('breadcrumbs')
	<li>Settings</li>
	<a href="/settings/integrations">Integrations</a>
	<li class="active">Virtualizor</li>
@stop

@section('content')
<form method="POST">
	<input type="hidden" name="_token" value="{{csrf_token()}}">
	<div class="row">
		  <div class="col-sm-12" id="div_container">
		      <div class="card" id="card_1" hidden>
						<input type="hidden" name="integration_id" value="" />
		        <div class="card-header">
		          <h3 class="card-title">Virtualizor</h3>
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
		            <label for="hostname">Hostname: </label>
		            <input type="text" name="hostname" id="hostname" class="form-control" value="{{old('hostname', Settings::get('virtualizor.hostname'))}}" required>
		          </div>
		          <div class="form-group">
		            <label for="port">Port: </label>
		            <input type="number" name="port" id="port" class="form-control" value="{{old('port', Settings::get('virtualizor.port'))}}" placeholder="4085">
		          </div>
		          <div class="form-group">
		            <label for="username">API Key: </label>
		            <input type="text" name="username" id="username" class="form-control" value="{{old('username', Settings::get('virtualizor.username'))}}" required>
		          </div>
		          <div class="form-group">
		            <label for="password">API Password: </label>
		            <input type="password" name="password" id="password" class="form-control" value="{{old('password', Settings::get('virtualizor.password'))}}" required>
		          </div>
		        </div>
		        <div class="card-footer">
							<button class="btn btn-danger float-left" type="button" onclick="DeleteContainer(1);">Delete</button>
		          <button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"></i> Update Settings</button>
		        </div>
		      </div>
		  </div>
		</div>
</form>

	<a href="/settings/integrations"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
	<button type="button" id="btn_add_server" class="btn btn-success float-right" onclick="AddContainer();">Add New Server</button>

	<input type="hidden" id="number_of_element" value="0" />
	<input type="hidden" id="number_of_server_group" value="0" />

	<!-- modal confirm delete -->
	<div class="modal fade" id="confirm">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">×</button>
					<h4 class="modal-title">Are you sure?</h4>
				</div>
				<div class="modal-body">
					<div class="text-center">
						Are you sure you wish to delete <span id="deleteText"></span>?
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default float-left btn-lrg" data-dismiss="modal">No</button>
					<button type="submit" class="btn btn-success btn-lrg" id="btn_delete" onclick="DoDelete()">Yes</button>
				</div>
			</div>
		</div>
	</div>
	<!-- modal confirm delete -->

@stop

@section ('css')
	<style>
		.checkbox.ssl {
			display: inline-block;
			margin-top: 0;
			margin-bottom: 0;
			margin-left: 10px;
		}
	  .imported {
	    background-color: #97f295 !important;
	  }
	</style>
@stop

@section('javascript')
<script>
  var integration_type = 'virtualizor';
  $(function() {
		@if($integrations)
			var x = 1;
			@foreach($integrations as $integration)
				AddContainer();
				$('#card_' + x).find($('input[name="hostname"]')).val('{{ $integration->hostname }}');
				$('#card_' + x).find($('input[name="port"]')).val('{{ $integration->port }}');
				$('#card_' + x).find($('input[name="username"]')).val('{{ $integration->username }}');
				$('#card_' + x).find($('input[name="password"]')).val('{{ $integration->password }}');
				$('#card_' + x).find($('input[name="integration_id"]')).val('{{ $integration->id }}');
				$('#number_of_element').val(x);
				x++;
			@endforeach
		@endif
	});

	var container_content = $('#div_container').html();
  function AddContainer()
  {
      if($('#number_of_element').val() == '0')
      {
          $('#card_1').removeAttr('hidden');
          $('#number_of_element').val('1');
          ClearNewElement(1);
          return;
      }
      var current_element = parseInt($('#number_of_element').val());
      var next_element = current_element + 1;
      var container_content_res = container_content.replace("card_1", "card_" + next_element);
      container_content_res = container_content_res.replace("DeleteContainer(1)", "DeleteContainer(" + next_element + ")");
      container_content_res = container_content_res.replace("SaveData(1, 'save')", "SaveData(" + next_element + ", 'save')");
      container_content_res = container_content_res.replace("div_message_1", "div_message_" + next_element);
      container_content_res = container_content_res.replace("hidden", "");
      $('#div_container').append(container_content_res);
      $('#number_of_element').val(next_element);
      ClearNewElement(next_element);
  }

  function ClearNewElement(number)
  {
      $('#card_' + number).find($('input[name="hostname"]')).val('');
      $('#card_' + number).find($('input[name="port"]')).val('');
      $('#card_' + number).find($('input[name="username"]')).val('');
      $('#card_' + number).find($('input[name="password"]')).val('');
  }

	function SaveData(number, operation)
  {
      var hostname = $('#card_' + number).find($('input[name="hostname"]')).val();
      var port = $('#card_' + number).find($('input[name="port"]')).val();
      var username = $('#card_' + number).find($('input[name="username"]')).val();
      var password = $('#card_' + number).find($('input[name="password"]')).val();

      //validation
      var errorMessage = '';
      if(hostname == '')
      {
          errorMessage += '<li>Hostname is required.</li>';
      }
      if(username == '')
      {
          errorMessage += '<li>Username is required.</li>';
      }
      if(password == '')
      {
          errorMessage += '<li>Password is required.</li>';
      }

      if(errorMessage != '')
      {
          SetStatus(number, false, errorMessage);
          return;
      }

      var integration_id = $('#card_' + number).find($('input[name="integration_id"]')).val();

      var url = '';
      if(operation == 'save')
      {
          url = '/settings/integrations/' + integration_type;
          $.ajax({
                url: url,
                data: {
                   "_token": "{{ csrf_token() }}",
                   'integration_id': integration_id,
                   'hostname': hostname,
                   'port': port,
                   'username': username,
                   'password': password,
                   'integration_type': integration_type
                },
                type: 'POST',
                beforeSend: function() {

                },
                success: function(response) {
                  if(response.success)
                  {
                      $('#card_' + number).find($('input[name="integration_id"]')).val(response.integration_id);
                      SetStatus(number, true, response.status);
                  }
                  else
                  {
                      errorMessage = '';
                      errorMessage = '<li>' + response.errors + '</li>';
                      SetStatus(number, false, errorMessage);
                  }
                },
                error: function(response) {
                   console.log(response);
                }
          });
      }

  }

	function DeleteContainer(number)
  {
      //delete server information from database
      var integration_id = $('#card_' + number).find($('input[name="integration_id"]')).val();
      if(integration_id != '')
      {
         $('#btn_delete').attr('onclick', 'DoDelete(' + number + ',' + integration_id + ')');
         $('#confirm').modal('show');
         return;
      }
      //end of delete
      $('#card_' + number).remove();
  }

  function DoDelete(number, integration_id)
  {
      $.ajax({
        url: '/settings/integrations/' + integration_type + '/' + integration_id,
        type: 'DELETE',
        success: function(result) {
          $('#confirm').modal('hide');
          $('#card_' + number).remove();
        }
      });
  }

  function SetStatus(number, success, message)
  {
      var htmlBlok = '';
      if(success)
      {
          htmlBlok = '<div class="alert alert-success">' + message + '</div>';
      }
      else
      {
          htmlBlok = '<div class="alert alert-danger"><ul>' + message + '</ul></div>';
      }
      $('#card_' + number).find($('div[name="div_message_' + number + '"]')).html(htmlBlok);
  }

</script>
@endsection
