@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Integrations')

@section('breadcrumbs')
  <a href="/settings/integrations">Integrations</a>
	<li class="breadcrumb-item active">DirectAdmin</li>
@stop

@section('content')
	<form method="post">
		<input type="hidden" name="_token" value="{{csrf_token()}}">
		<div class="row">
		  <div class="col-sm-12" id="div_container">

        <!-- start card -->
		    <div class="card" id="card_1" hidden>
          <input type="hidden" name="integration_id" value="" />
		      <div class="card-header" data-card-widget="collapse">
		          <h3 class="card-title">
		          DirectAdmin Hostname Here
		          </h3>
		              <div class="card-tools">
		                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fa fa-minus"></i></button>
		              </div>
		      </div>
					<div class="card-body">
        		<div class="row">
        		  <div class="col-sm-6">
        		    <div class="card-body">
                    <div name="div_message_1">
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
                    </div>
        		        <div class="form-group">
        		          <label for="hostname">DirectAdmin IP Address: </label>
        		          <input type="text" name="hostname" class="form-control" value="{{old('hostname', Settings::get('directadmin.hostname'))}}" required>
        		        </div>
        		        <div class="form-group">
        		          <label for="port">Port: </label>
        		          <span class="checkbox ssl">
        		            <label>
        		              <input type="checkbox" name="https" {{ old('https', Settings::get('directadmin.https')) == false ?: 'checked' }}>
        		              Use HTTPS
        		            </label>
        		          </span>
        		          <input type="number" min="0" max="65535" name="port" class="form-control" value="{{old('port', Settings::get('directadmin.port'))}}" placeholder="2222">
        		        </div>
        		        <div class="form-group">
        		          <label for="username">Username: </label>
        		          <input type="text" name="username" class="form-control" value="{{old('username', Settings::get('directadmin.username'))}}" required>
        		        </div>
        		        <div class="form-group">
        		          <label for="password">Password: </label>
        		          <input type="password" name="password" class="form-control" value="{{old('password', Settings::get('directadmin.password'))}}" required>
        		        </div>
        						Server Groups:
                    <div id="div_server_group_1">
                      <!--display items for server groups -->
                    </div>
                    <br>
        						<div class="form-group">
        		          <button type="button" id="btn_import_customer_1" class="btn btn-default" onclick="ShowAccountData(1, 'import_customers');"> <i class="fa fa-arrow-circle-o-left"></i> Import Customers</button>
        						</div>
        		    </div>
        		  </div>
        		  <div class="col-sm-6">
        		    <div class="card-body">
        						<div class="row">
                    <div class="col-sm-6">
                    <div class="form-group">
                      <label for="nameserver-1">Nameserver 1: </label>
                      <input type="text" name="nameserver-1" class="form-control" required>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="nameserver-ip-1">Nameserver IP 1: </label>
                      <input type="text" name="nameserver-ip-1" class="form-control" required>
                    </div>
                  </div>
                </div>
                <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                      <label for="nameserver-2">Nameserver 2: </label>
                      <input type="text" name="nameserver-2" class="form-control" required>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="nameserver-ip-2">Nameserver IP 2: </label>
                      <input type="text" name="nameserver-ip-2" class="form-control" required>
                    </div>
                  </div>
                  </div>
                    <div class="row">
                    <div class="col-sm-6">
                    <div class="form-group">
                      <label for="nameserver-3">Nameserver 3: </label>
                      <input type="text" name="nameserver-3" class="form-control" required>
                    </div>
                  </div>
                  <div class="col-sm-6"
                    <div class="form-group">
                      <label for="nameserver-ip-3">Nameserver IP 3: </label>
                      <input type="text" name="nameserver-ip-3" class="form-control" required>
                    </div>
                  </div>
                  <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="nameserver-4">Nameserver 4: </label>
                      <input type="text" name="nameserver-4" class="form-control" required>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="nameserver-ip-4">Nameserver IP 4: </label>
                      <input type="text" name="nameserver-ip-4" class="form-control" required>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label for="nameserver-ip-4">Qty: </label>
                  <input type="number" min="0" max="999999999" name="qty" class="form-control" required>
                </div>
        		    </div>
        		  </div>
        		</div>
	         </div>
           <div class="card-footer">
             <button class="btn btn-danger float-left" type="button" onclick="DeleteContainer(1);">Delete</button>
             <button class="btn btn-success float-right" type="button" onclick="SaveData(1, 'save');">Save</button>
           </div>
       </div>
      <!-- end card -->

     </div>
   </div>
  </form>

	<a href="/settings/integrations"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
	<button type="button" class="btn btn-success float-right" data-toggle="modal" data-target="#modal-group">Add New Group</button>
	<button type="submit" class="btn btn-success float-right mr-2" onclick="AddContainer();">Add New Server</button>

	<div class="modal fade" id="modal-group">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Add New Server Group</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
							<div class="form-group">
				        <label for="nameserver-4">Server Group Name: </label>
				        <input type="text" name="server-group-name" class="form-control" required>
				      </div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="AddServerGroup();">Save changes</button>
            </div>
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->

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

      <!-- modal import -->
      <div class="modal fade" id="import">
    		<div class="modal-dialog modal-xl">
    			<div class="modal-content">
    				<div class="modal-header">
    					<button type="button" class="close" data-dismiss="modal">×</button>
    					<h4 class="modal-title">Import Customers</h4>
    				</div>
    				<div class="modal-body" id="div_import_body">
    					<div class="text-center">
    						<i class="fa fa-spinner fa-spin" style="font-size:50px"></i>
    					</div>
    				</div>
    				<div class="modal-footer" id="div_import_footer">
    					<button type="button" id="btn_cancel" class="btn btn-default float-left btn-lrg" data-dismiss="modal">Cancel</button>
    					<button type="submit" class="btn btn-success btn-lrg" id="btn_continue" style="display:none;" onclick="ShowImportOption();">Continue</button>
    				</div>
    			</div>
    		</div>
    	</div>
      <!-- modal confirm import -->
@stop

@section('javascript')
<script>
  var integration_type = 'directadmin';
  $(function() {
    @if($integrations)
      var x = 1;
      @foreach($integrations as $integration)
        AddContainer();
        $('#card_' + x).find($('input[name="hostname"]')).val('{{ $integration->hostname }}');
        @if($integration->https == 1)
          $('#card_' + x).find($('input[name="https"]')).prop('checked', true);
        @else
          $('#card_' + x).find($('input[name="https"]')).prop('checked', false);
        @endif
        $('#card_' + x).find($('input[name="port"]')).val('{{ $integration->port }}');
        $('#card_' + x).find($('input[name="username"]')).val('{{ $integration->username }}');
        $('#card_' + x).find($('input[name="password"]')).val('{{ $integration->password }}');
        $('#card_' + x).find($('input[name="nameserver-1"]')).val('{{ $integration->nameserver_1 }}');
        $('#card_' + x).find($('input[name="nameserver-ip-1"]')).val('{{ $integration->nameserver_ip_1 }}');
        $('#card_' + x).find($('input[name="nameserver-2"]')).val('{{ $integration->nameserver_2 }}');
        $('#card_' + x).find($('input[name="nameserver-ip-2"]')).val('{{ $integration->nameserver_ip_2 }}');
        $('#card_' + x).find($('input[name="nameserver-3"]')).val('{{ $integration->nameserver_3 }}');
        $('#card_' + x).find($('input[name="nameserver-ip-3"]')).val('{{ $integration->nameserver_ip_3 }}');
        $('#card_' + x).find($('input[name="nameserver-4"]')).val('{{ $integration->nameserver_4 }}');
        $('#card_' + x).find($('input[name="nameserver-ip-4"]')).val('{{ $integration->nameserver_ip_4 }}');
        $('#card_' + x).find($('input[name="qty"]')).val('{{ $integration->qty }}');

        var server_group_available = '{{ $integration->server_group_available }}';
        var server_group_selected = '{{ $integration->server_group_selected }}';
        @if($integration->server_group_available != '')
          var server_group_available_arr = server_group_available.split(",");
          var server_group_selected_arr = server_group_selected.split(",");
          for(var y = 0; y < server_group_available_arr.length; y++)
          {
              var num_element = y+1;
              var checked = '';
              if(server_group_selected_arr.includes(server_group_available_arr[y])) checked = 'checked';
              $('#div_server_group_' + x).append('\
                                              <div class="form-check">\
                                                <input class="form-check-input" type="checkbox" name="server_group_check_' + num_element + '" value="' + server_group_available_arr[y] + '" ' + checked + '>\
                                                <label class="form-check-label" name="server_group_name_' + num_element + '">' + server_group_available_arr[y] + '</label>\
                                              </div>\
                                            ');
          }
          $('#number_of_server_group').val(server_group_available_arr.length);
        @endif
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
      container_content_res = container_content_res.replace("SaveData(1, 'import_customers')", "SaveData(" + next_element + ", 'import_customers')");
      container_content_res = container_content_res.replace("ShowAccountData(1, 'import_customers')", "ShowAccountData(" + next_element + ", 'import_customers')");
      container_content_res = container_content_res.replace("div_server_group_1", "div_server_group_" + next_element);
      container_content_res = container_content_res.replace("btn_import_customer_1", "div_server_group_" + next_element);
      container_content_res = container_content_res.replace("div_message_1", "div_message_" + next_element);
      container_content_res = container_content_res.replace("hidden", "");
      $('#div_container').append(container_content_res);
      for(var x = 0; x < $('#number_of_server_group').val(); x++)
      {
          current_group = current_group.replace('checked', '');
      }
      $('#div_server_group_' + next_element).append(current_group);
      $('#number_of_element').val(next_element);
      ClearNewElement(next_element);
      current_group = $('#div_server_group_1').html();
  }

  function ClearNewElement(number)
  {
      $('#card_' + number).find($('input[name="hostname"]')).val('');
      $('#card_' + number).find($('input[name="https"]')).prop('checked', false);
      $('#card_' + number).find($('input[name="port"]')).val('');
      $('#card_' + number).find($('input[name="username"]')).val('');
      $('#card_' + number).find($('input[name="password"]')).val('');
      $('#card_' + number).find($('input[name="nameserver-1"]')).val('');
      $('#card_' + number).find($('input[name="nameserver-ip-1"]')).val('');
      $('#card_' + number).find($('input[name="nameserver-2"]')).val('');
      $('#card_' + number).find($('input[name="nameserver-ip-2"]')).val('');
      $('#card_' + number).find($('input[name="nameserver-3"]')).val('');
      $('#card_' + number).find($('input[name="nameserver-ip-3"]')).val('');
      $('#card_' + number).find($('input[name="nameserver-4"]')).val('');
      $('#card_' + number).find($('input[name="nameserver-ip-4"]')).val('');
      $('#card_' + number).find($('input[name="qty"]')).val('');
  }

  var current_group = $('#div_server_group_1').html();
  var number_of_server_group = $('#number_of_server_group').val();
  function AddServerGroup()
  {
      number_of_server_group = $('#number_of_server_group').val();
      var name = sanitize($('input[name="server-group-name"]').val());
      number_of_server_group++;
      for(var x = 1; x <= $('#number_of_element').val(); x++)
      {
          $('#div_server_group_' + x).append('\
                                          <div class="form-check">\
                                            <input class="form-check-input" type="checkbox" name="server_group_check_' + number_of_server_group + '" value="' + name + '" >\
                                            <label class="form-check-label" name="server_group_name_' + number_of_server_group + '">' + name + '</label>\
                                          </div>\
                                        ');

          current_group = $('#div_server_group_' + x).html();
      }
      $('input[name="server-group-name"]').val('');
      $('#number_of_server_group').val(number_of_server_group);
  }

  function SaveData(number, operation)
  {
      var hostname = $('#card_' + number).find($('input[name="hostname"]')).val();
      var https = 0;
      if($('#card_' + number).find($('input[name="https"]:checked')).val() == 'on')
      {
          https = 1;
      }
      var port = $('#card_' + number).find($('input[name="port"]')).val();
      var username = $('#card_' + number).find($('input[name="username"]')).val();
      var password = $('#card_' + number).find($('input[name="password"]')).val();
      var nameserver_1 = $('#card_' + number).find($('input[name="nameserver-1"]')).val();
      var nameserver_2 = $('#card_' + number).find($('input[name="nameserver-2"]')).val();
      var nameserver_3 = $('#card_' + number).find($('input[name="nameserver-3"]')).val();
      var nameserver_4 = $('#card_' + number).find($('input[name="nameserver-4"]')).val();
      var nameserver_ip_1 = $('#card_' + number).find($('input[name="nameserver-ip-1"]')).val();
      var nameserver_ip_2 = $('#card_' + number).find($('input[name="nameserver-ip-2"]')).val();
      var nameserver_ip_3 = $('#card_' + number).find($('input[name="nameserver-ip-3"]')).val();
      var nameserver_ip_4 = $('#card_' + number).find($('input[name="nameserver-ip-4"]')).val();
      var qty = $('#card_' + number).find($('input[name="qty"]')).val();

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
      if(qty == '')
      {
          errorMessage += '<li>Qty is required.</li>';
      }

      if(errorMessage != '')
      {
          SetStatus(number, false, errorMessage);
          return;
      }

      //get server group
      var server_group_available = '';
      var server_group_selected = '';
      for(var x = 1; x <= $('#number_of_server_group').val(); x++)
      {
          if(server_group_available != '')
          {
              server_group_available += ',';
          }
          server_group_available += $('#card_' + number).find($('input[name="server_group_check_'+ x + '"]')).val();

          if($('#card_' + number).find($('input[name="server_group_check_'+ x + '"]')).prop('checked'))
          {
              if(server_group_selected != '')
              {
                  server_group_selected += ',';
              }
              server_group_selected += $('#card_' + number).find($('input[name="server_group_check_'+ x + '"]')).val();
          }
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
                   'https': https,
                   'port': port,
                   'username': username,
                   'password': password,
                   'nameserver_1': nameserver_1,
                   'nameserver_2': nameserver_2,
                   'nameserver_3': nameserver_3,
                   'nameserver_4': nameserver_4,
                   'nameserver_ip_1': nameserver_ip_1,
                   'nameserver_ip_2': nameserver_ip_2,
                   'nameserver_ip_3': nameserver_ip_3,
                   'nameserver_ip_4': nameserver_ip_4,
                   'qty': qty,
                   'server_group_selected': server_group_selected,
                   'server_group_available': server_group_available,
                   'integration_type': integration_type
                },
                type: 'POST',
                beforeSend: function() {
                  $('#btn_import_customer_' + number).html('<i class="fa fa fa-circle-o-notch"></i> Loading...');
                  $('#btn_import_customer_' + number).prop('disabled', true);
                },
                success: function(response) {
                  $('#btn_import_customer_' + number).html('<i class="fa fa-arrow-circle-o-left"></i> Import Customers');
                  $('#btn_import_customer_' + number).prop('disabled', false);
                  if(response.success)
                  {
                      $('#card_' + number).find($('input[name="integration_id"]')).val(response.integration_id);
                      SetStatus(number, true, response.status);
                  }
                  else
                  {
                      errorMessage = '';
                      /* $.each(response.errors, function( key, value ) {
                        errorMessage += '<li>' + key + ": " + value + '</li>';
                      }); */
                      errorMessage = '<li>' + response.errors + '</li>';
                      SetStatus(number, false, errorMessage);
                  }
                },
                error: function(response) {
                   console.log(response);
                   $('#btn_import_customer_' + number).html('<i class="fa fa-arrow-circle-o-left"></i> Import Customers');
                   $('#btn_import_customer_' + number).prop('disabled', false);
                }
          });
      }
      else if(operation == 'import_customers')
      {
          url = '/settings/integrations/import_customers/' + integration_type;
          $.ajax({
                url: url,
                data: {
                   "_token": "{{ csrf_token() }}",
                   'integration_id': integration_id,
                   'hostname': hostname,
                   'https': https,
                   'port': port,
                   'username': username,
                   'password': password,
                   'nameserver_1': nameserver_1,
                   'nameserver_2': nameserver_2,
                   'nameserver_3': nameserver_3,
                   'nameserver_4': nameserver_4,
                   'nameserver_ip_1': nameserver_ip_1,
                   'nameserver_ip_2': nameserver_ip_2,
                   'nameserver_ip_3': nameserver_ip_3,
                   'nameserver_ip_4': nameserver_ip_4,
                   'qty': qty,
                   'server_group_selected': server_group_selected,
                   'server_group_available': server_group_available,
                   'integration_type': integration_type,
                   'check_usernames': check_usernames,
                   'client_welcome_email': $('#client_welcome_email').val(),
                   'client_welcome_email_option': $('#client_welcome_email_option').val(),
                   'reset_password': $('#reset_password').val(),
                   'service_welcome_email': $('#service_welcome_email').val(),
                   'recurring_billing_info': $('#recurring_billing_info').val()
                },
                type: 'POST',
                beforeSend: function() {
                  $('#btn_continue').html('<i class="fa fa fa-circle-o-notch"></i> Loading...');
                  $('#btn_continue').prop('disabled', true);
                },
                success: function(response) {
                  $('#btn_continue').html('Continue');
                  $('#btn_continue').prop('disabled', false);
                  if(response.success)
                  {
                      $('#div_import_body').html('\
                                                  <div class="text-center">\
                                                    <h3>' + response.status + '</h3>\
                                                  </div>\
                                                  ');
                      $('#card_' + number).find($('input[name="integration_id"]')).val(response.integration_id);
                      $('#btn_continue').attr('style', 'display:none;');
                      $('#btn_cancel').removeClass('btn-default');
                      $('#btn_cancel').addClass('btn-success');
                      $('#btn_cancel').html('Done');
                  }
                  else
                  {
                      errorMessage = '';
                      /* $.each(response.errors, function( key, value ) {
                        errorMessage += '<li>' + key + ": " + value + '</li>';
                      }); */
                      errorMessage = '<li>' + response.errors + '</li>';
                      $('#div_import_body').html('\
                                                  <div class="text-center">\
                                                    <h3>' + response.errorMessage + '</h3>\
                                                  </div>\
                                                  ');
                  }
                },
                error: function(response) {
                   console.log(response);
                   $('#btn_import_customer_' + number).html('<i class="fa fa-arrow-circle-o-left"></i> Import Customers');
                   $('#btn_import_customer_' + number).prop('disabled', false);
                }
          });
      }

  }

  function ShowAccountData(number, operation)
  {
      $('#btn_cancel').removeClass('btn-success');
      $('#btn_cancel').addClass('btn-default');
      $('#btn_cancel').html('Cancel');
      $('#div_import_body').html('\
                                  <div class="text-center">\
                                    <i class="fa fa-spinner fa-spin" style="font-size:50px"></i>\
                                  </div>\
                                  ');
      $('#import').modal('show');

      var integration_id = $('#card_' + number).find($('input[name="integration_id"]')).val();
      var url = '/settings/integrations/import_customers/directadmin/step_1/' + integration_id;
      $.ajax({
            url: url,
            data: {
            },
            type: 'GET',
            beforeSend: function() {
              $('#btn_continue').attr('style', 'display:none;');
            },
            success: function(response) {
              console.log(response);
              if(response.success)
              {
                  var data = '';
                  for(var x = 0; x < response.data.length; x++)
                  {
                      var imported = '';
                      var importedCheck = '<i class="fas fa-times"></i>';
                      var importedCheckBox = '<input type="checkbox" name="check_username[]" value="' + response.data[x]['username'] + '" />';
                      if(response.data[x]['imported'] == 1)
                      {
                          imported = 'imported';
                          importedCheck = '<i class="fas fa-check"></i>';
                          importedCheckBox = '';
                      }
                      data += '\
                                <tr class=' + imported + '>\
                                  <td>' + importedCheck + '</td>\
                                  <td id="domain_' + response.data[x]['username'] + '">' + response.data[x]['domain'] + '</td>\
                                  <td id="ip_' + response.data[x]['username'] + '">' + response.data[x]['ip'] + '</td>\
                                  <td id="username_' + response.data[x]['username'] + '">' + response.data[x]['username'] + '</td>\
                                  <td id="package_' + response.data[x]['username'] + '">' + response.data[x]['package'] + '</td>\
                                  <td id="status_' + response.data[x]['username'] + '">Active</td>\
                                  <td id="datecreated_' + response.data[x]['username'] + '">' + response.data[x]['date_created'] + '</td>\
                                  <td>' + importedCheckBox + '</td>\
                                <tr>\
                              ';
                  }
                  var content = '\
                                  <table id="accountList" class="table table-bordered table-striped">\
                                    <thead>\
                                      <tr>\
                                        <th></th>\
                                        <th>Domain</th>\
                                        <th>Primary IP</th>\
                                        <th>Username</th>\
                                        <th>Package</th>\
                                        <th>Status</th>\
                                        <th>Created</th>\
                                        <th>Import/Sync</th>\
                                      </tr>\
                                    </thead>\
                                    <tbody>\
                                        ' + data + '\
                                    </tbody>\
                                  </table>\
                                  ';

                  $('#div_import_body').html(content);
                  $('#btn_continue').attr('style', '');
              }
              else
              {
                  console.log(response.errors);
                  $('#btn_continue').attr('style', 'display:none;');
                  $('#div_import_body').html('\
                                                <div class="text-center">\
                                                  <h1>' + response.errors + '</h1>\
                                                </div>\
                                            ');
              }
            },
            error: function(response) {
               console.log(response);
               $('#btn_continue').attr('style', 'display:none;');
            }
      });
      $('#btn_continue').attr('onclick', 'ShowImportOption(' + number + ')');
  }

  var check_usernames = [];
  function ShowImportOption(number)
  {
      check_usernames = [];
      $.each($("input[name='check_username[]']:checked"), function(){
          //check_usernames.push($(this).val());
          check_usernames.push({
            domain: $('#domain_' + $(this).val()).html(),
            ip: $('#ip_' + $(this).val()).html(),
            username: $('#username_' + $(this).val()).html(),
            package: $('#package_' + $(this).val()).html(),
            status: $('#status_' + $(this).val()).html(),
            date_created: $('#datecreated_' + $(this).val()).html()
        });
      });
      if(check_usernames.length == 0)
      {
          alert('Please select at least one to sync');
          return;
      }

      //show selected data with options
      var data = '';
      $.each(check_usernames, function(index, value){
          data += '\
                <tr>\
                  <td id="domain_' + value.username + '">' + value.domain + '</td>\
                  <td id="ip_' + value.username + '">' + value.ip + '</td>\
                  <td id="username_' + value.username + '">' + value.username + '</td>\
                  <td id="package_' + value.username + '">' + value.package + '</td>\
                  <td id="status_' + value.username + '">' + value.status + '</td>\
                  <td id="datecreated_' + value.username + '">' + value.date_created + '</td>\
                <tr>\
              ';
      });

      var content = '\
                      <table id="accountList" class="table table-bordered table-striped">\
                        <thead>\
                          <tr>\
                            <th>Domain</th>\
                            <th>Primary IP</th>\
                            <th>Username</th>\
                            <th>Package</th>\
                            <th>Status</th>\
                            <th>Created</th>\
                          </tr>\
                        </thead>\
                        <tbody>\
                            ' + data + '\
                        </tbody>\
                      </table>\
                      <br/>\
                      <h3>New Account Import Settings</h3>\
                      <div class="form-inline">\
                        <div class="form-group">\
                          <b>Send Client Welcome Email</b>\
                        </div>\
                        &nbsp;&nbsp;\
                        <div class="form-group">\
                          <select id="client_welcome_email" class="form-control">\
                            <option value="yes">Yes</option>\
                            <option value="no">No</option>\
                          </select>\
                        </div>\
                        <div class="form-group">\
                          <select id="client_welcome_email_option" class="form-control">\
                            <option value="automated">Automated Password Reset</option>\
                            <option value="signup" selected="selected">Client Signup Email</option>\
                          </select>\
                        </div>\
                      </div>\
                      \
                      <div class="form-inline">\
                        <div class="form-group">\
                          <b>Reset Service Account Passwords</b>\
                        </div>\
                        &nbsp;&nbsp;\
                        <div class="form-group">\
                          <select id="reset_password" class="form-control">\
                            <option value="yes">Yes</option>\
                            <option value="no">No</option>\
                          </select>\
                        </div>\
                      </div>\
                      \
                      <div class="form-inline">\
                      \
                        <div class="form-group">\
                          <b>Send Service Welcome Email</b>\
                        </div>\
                        &nbsp;&nbsp;\
                        <div class="form-group">\
                          <select id="service_welcome_email" class="form-control">\
                            <option value="yes">Yes</option>\
                            <option value="no">No</option>\
                          </select>\
                        </div>\
                        \
                      </div>\
                      \
                      ';

      $('#div_import_body').html(content);
      $('#btn_continue').attr('onclick', 'SaveData(' + number + ', "import_customers")');
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

  function sanitize(string)
  {
      const map = {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#x27;',
          "/": '&#x2F;',
      };
      const reg = /[&<>"'/]/ig;
      return string.replace(reg, (match)=>(map[match]));
  }

</script>
@stop

@section('css')
<style>
  .imported {
    background-color: #97f295 !important;
  }
</style>
@stop
