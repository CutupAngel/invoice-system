@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Integrations')

@section('breadcrumbs')
	<li>Settings</li>
	<a href="/settings/integrations">Integrations</a>
	<li class="active">cPanel/WHM</li>
@stop

@section('content')
	@php
		$x = 1;
	@endphp
	<div class="row">
		<div id="form_container" class="col-md-12">
			@foreach($integrationCpanels as $cpanel)
				<form class="card form-card" id="card_{{ $x }}" action="/settings/integrations/cpanel/{{ $cpanel->id }}" method="post">
					<input type="hidden" name="_method" value="PUT">
					<input type="hidden" name="id" value="{{ $cpanel->id }}">
					<div class="card-header" data-card-widget="collapse">
						<h3 class="card-title">cPanel/WHM</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fa fa-minus"></i></button>
						</div>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<label>Server Name <span class="text-danger">*</span></label>
									<input type="text" name="name" class="form-control" value="{{ $cpanel->name }}" placeholder="Enter server name" required>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>IP Address/Hostname <span class="text-danger">*</span></label>
									<span class="checkbox ssl">
										<label>
											<input type="checkbox" name="https" value="1" {{ $cpanel->https ? 'checked' : '' }}>&nbsp;&nbsp;Use HTTPS
										</label>
									</span>
									<input type="text" name="hostname" class="form-control" value="{{ $cpanel->hostname }}" placeholder="Enter IP Address/Hostname" required>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label for="port">Port <span class="text-danger">*</span></label>
									<input type="number" name="port" class="form-control" value="{{ $cpanel->port }}" required>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Username <span class="text-danger">*</span></label>
									<input type="text" name="username" class="form-control" value="{{ $cpanel->username }}" placeholder="Enter username" required>
								</div>
							</div>
							<div class="col-md-12">
								<label for="accesskey">Remote Access Key <span class="text-danger">*</span></label>
								<textarea name="access_key" class="form-control" required>{{ $cpanel->access_key }}</textarea>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<label>Nameserver 1</label>
									<input type="text" name="nameserver_1" class="form-control" value="{{ $cpanel->nameserver_1 }}">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Nameserver 2</label>
									<input type="text" name="nameserver_2" class="form-control" value="{{ $cpanel->nameserver_2 }}">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Nameserver 3</label>
									<input type="text" name="nameserver_3" class="form-control" value="{{ $cpanel->nameserver_3 }}">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Nameserver 4</label>
									<input type="text" name="nameserver_4" class="form-control" value="{{ $cpanel->nameserver_4 }}">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<label>Nameserver IP 1</label>
									<input type="text" name="nameserver_ip_1" class="form-control" value="{{ $cpanel->nameserver_ip_1 }}">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Nameserver IP 2</label>
									<input type="text" name="nameserver_ip_2" class="form-control" value="{{ $cpanel->nameserver_ip_2 }}">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Nameserver IP 3</label>
									<input type="text" name="nameserver_ip_3" class="form-control" value="{{ $cpanel->nameserver_ip_3 }}">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Nameserver IP 4</label>
									<input type="text" name="nameserver_ip_4" class="form-control" value="{{ $cpanel->nameserver_ip_4 }}">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<label>Qty</label>
									<input type="number" min="0" max="999999999" name="qty" class="form-control" value="{{ $cpanel->qty }}">
								</div>
							</div>
						</div>
						<br>
						<div class="form-group">
							<button type="button" id="btn_import_customer_{{ $x }}" class="btn btn-default" onclick="ShowAccountData({{ $x }}, 'import_customers');"> <i class="fa fa-arrow-circle-o-left"></i> Import Customers</button>
						</div>
					</div>
					<div class="card-footer">
						<button class="btn btn-danger float-left btn-delete" data-url="/settings/integrations/cpanel/{{ $cpanel->id }}" type="button">Delete</button>
						<button class="btn btn-success float-right" type="submit">Update</button>
					</div>
				</form>
				@php
					$x++;
				@endphp
			@endforeach
			<input type="hidden" id="num_element" value="{{ $x }}" />
		</div>
	</div>
	<a href="/settings/integrations"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
	<button type="button" id="btn_add_server" class="btn btn-success float-right mr-2">Add New Server</button>

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
	<script type="text/javascript">
	  var integration_type = 'cpanel';
		(function ($) {
		    let request;
            const CSRFTOKEN = document.head.querySelector('meta[name=csrf-token]').content;
			const btnAddServer = $('#btn_add_server');
			const formContainer = $('#form_container');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': CSRFTOKEN
                }
            });

			btnAddServer.on('click', addForm);

			$(document).on('click', '.btn-delete', function (e) {
			    const self = $(this);
				const deleteUrl = self.data('url');
				const thisForm = self.closest('.form-card');
				if (deleteUrl) {
					makeRequest(thisForm, {url: deleteUrl, data: {_method: 'DELETE'}}, function () {
                        location.reload();
                    }, function (xhr) {
					    const errorElement = '<div class="alert alert-danger"><strong>Error: '+ xhr.status +'</strong>'+ xhr.statusText +' <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
						thisForm.prepend(errorElement);
                    });
				} else {
				    thisForm.remove();
				}
            });
			$(document).on('submit', '.form-card', function (e) {
			    e.preventDefault();
				const self = $(this);
				makeRequest(self, null, function () {
                    location.reload();
                }, function (xhr) {
					if (xhr.status === 422) {
						const errors = xhr.responseJSON.errors;
						Object.keys(errors).forEach(function (key) {
							let input = self.find('input[name='+key+']');
							$(".invalid-feedback").remove();
							input.after('<div class="invalid-feedback">'+ errors[key][0] +'</div>');
							input.addClass('is-invalid');
                        });
					} else {
                        const errorElement = '<div class="alert alert-danger"><strong>Error: '+ xhr.status +'</strong>'+ xhr.statusText +' <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
                        self.prepend(errorElement);
					}
                });
            });
			function addForm() {
					var number_of_element = $('#num_element').val();

			    const form = '<form class="card form-card" id="card_' + number_of_element + '" action="" method="post">' +
                    '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                    '<div class="card-header" data-card-widget="collapse">' +
                    '<h3 class="card-title">cPanel/WHM</h3>' +
                    '<div class="card-tools">' +
                    '<button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fa fa-minus"></i></button>' +
                    '</div>' +
                    '</div>' +
                    '<div class="card-body">' +
                    '<div class="row">' +
                    '<div class="col-md-3">' +
                    '<div class="form-group">' +
                    '<label>Server Name <span class="text-danger">*</span></label>' +
                    '<input type="text" name="name" class="form-control" placeholder="Enter server name" required>' +
                    '</div>' +
                    '</div>' +
                    '<div class="col-md-3">' +
                    '<div class="form-group">' +
                    '<label>IP Address/Hostname <span class="text-danger">*</span></label>' +
                    '<span class="checkbox ssl">' +
                    '<label>' +
                    '<input type="checkbox" name="https" value="1">&nbsp;&nbsp;Use HTTPS' +
                    '</label>' +
                    '</span>' +
                    '<input type="text" name="hostname" class="form-control" placeholder="Enter IP Address/Hostname" required>' +
                    '</div>' +
                    '</div>' +
                    '<div class="col-md-3">' +
                    '<div class="form-group">' +
                    '<label for="port">Port <span class="text-danger">*</span></label>' +
                    '<input type="number" name="port" class="form-control" required>' +
                    '</div>' +
                    '</div>' +
                    '<div class="col-md-3">' +
                    '<div class="form-group">' +
                    '<label>Username <span class="text-danger">*</span></label>' +
                    '<input type="text" name="username" class="form-control" placeholder="Enter username" required>' +
                    '</div>' +
                    '</div>' +
                    '<div class="col-md-12">' +
                    '<label for="access_key">Remote Access Key <span class="text-danger">*</span></label>' +
                    '<textarea name="access_key" class="form-control" required></textarea>' +
                    '</div>' +
                    '</div>' +
										'<div class="row">' +
										'<div class="col-md-3">' +
										'<div class="form-group">' +
										'<label>Nameserver 1</label>' +
										'<input type="text" name="nameserver_1" class="form-control" value="">' +
										'</div>' +
										'</div>' +
										'<div class="col-md-3">' +
										'<div class="form-group">' +
										'<label>Nameserver 2</label>' +
										'<input type="text" name="nameserver_2" class="form-control" value="">' +
										'</div>' +
										'</div>' +
										'<div class="col-md-3">' +
										'<div class="form-group">' +
										'<label>Nameserver 3</label>' +
										'<input type="text" name="nameserver_3" class="form-control" value="">' +
										'</div>' +
										'</div>' +
										'<div class="col-md-3">' +
										'<div class="form-group">' +
										'<label>Nameserver 4</label>' +
										'<input type="text" name="nameserver_4" class="form-control" value="">' +
										'</div>' +
										'</div>' +
										'</div>' +
										'<div class="row">' +
										'<div class="col-md-3">' +
										'<div class="form-group">' +
										'<label>Nameserver IP 1</label>' +
										'<input type="text" name="nameserver_ip_1" class="form-control" value="">' +
										'</div>' +
										'</div>' +
										'<div class="col-md-3">' +
										'<div class="form-group">' +
										'<label>Nameserver IP 2</label>' +
										'<input type="text" name="nameserver_ip_2" class="form-control" value="">' +
										'</div>' +
										'</div>' +
										'<div class="col-md-3">' +
										'<div class="form-group">' +
										'<label>Nameserver IP 3</label>' +
										'<input type="text" name="nameserver_ip_3" class="form-control" value="">' +
										'</div>' +
										'</div>' +
										'<div class="col-md-3">' +
										'<div class="form-group">' +
										'<label>Nameserver IP 4</label>' +
										'<input type="text" name="nameserver_ip_4" class="form-control" value="">' +
										'</div>' +
										'</div>' +
										'</div>' +
										'<div class="row">' +
										'<div class="col-md-3">' +
										'<div class="form-group">' +
										'<label>Qty</label>' +
										'<input type="number" min="0" max="999999999" name="qty" class="form-control" value="">' +
										'</div>' +
										'</div>' +
										'</div>' +
                    '</div>' +
                    '<div class="card-footer">' +
                    '<button class="btn btn-danger btn-delete float-left" type="button">Delete</button>' +
                    '<button class="btn btn-success float-right" type="submit">Save</button>' +
                    '</div>' +
                    '</form>';

				formContainer.append(form);
				number_of_element++;
				$('#num_element').val(number_of_element);
            }

            function makeRequest(form, options, callbackSuccess, callbackError) {
				if (request) request.abort();

				const inputs = form.find('input, button');
				let url, data;

				if (options) {
				    url = options.url;
					data = options.data;
				} else {
				    url = form.attr('action');
				    data = form.serialize();
				}

				inputs.prop('disabled', true);

				request = $.ajax({
					url: url,
					method: 'post',
					data: data,
				});

				request.done(callbackSuccess);
				request.fail(callbackError);
				request.always(function () {
					inputs.prop('disabled', false);
                });
            }
        })(jQuery);

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

			      var integration_id = $('#card_' + number).find($('input[name="id"]')).val();
			      var url = '/settings/integrations/import_customers/cpanel/step_1/' + integration_id;
			      $.ajax({
			            url: url,
			            data: {
			            },
			            type: 'GET',
			            beforeSend: function() {
			              $('#btn_continue').attr('style', 'display:none;');
			            },
			            success: function(response) {
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

				function SaveData(number, operation)
			  {
						var integration_id = $('#card_' + number).find($('input[name="id"]')).val();
						var hostname = $('#card_' + number).find($('input[name="hostname"]')).val();
						var https = 0;
						if($('#card_' + number).find($('input[name="https"]:checked')).val() == 'on')
						{
								https = 1;
						}
						var port = $('#card_' + number).find($('input[name="port"]')).val();
						var username = $('#card_' + number).find($('input[name="username"]')).val();
						var access_key = $('#card_' + number).find($('input[name="access_key"]')).val();

						if(operation == 'import_customers')
						{
						    url = '/settings/integrations/import_customers/' + integration_type;
						    $.ajax({
						          url: url,
						          data: {
						             "_token": "{{ csrf_token() }}",
						             'id': integration_id,
						             'hostname': hostname,
						             'https': https,
						             'port': port,
						             'username': username,
						             'access_key': access_key,
			                   'check_usernames': check_usernames,
			                   'client_welcome_email': $('#client_welcome_email').val(),
			                   'client_welcome_email_option': $('#client_welcome_email_option').val(),
			                   'reset_password': $('#reset_password').val(),
			                   'service_welcome_email': $('#service_welcome_email').val(),
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
	</script>
@stop
