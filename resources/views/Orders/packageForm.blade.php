@extends('Common.template')

@section('title', ' Packages')
@section('page.title', 'Packages')
@section('page.subtitle')
	{{$type}} Package
@stop

@section('breadcrumbs')
	<a href="/orders">View Packages</a>
	<li class="breadcrumb-item"><a href="/orders/{{$group->id}}">{{$group->name}}</a></li>
	<li class="breadcrumb-item active">{{$type}} Package</li>
@stop

@section ('content')
	<form method="post" enctype="multipart/form-data">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">

		<div class="row">
			<div class="col-md-6">
				<div class="card">
						<div class="card-header">
							<h3 class="card-title">
								Package Info
							</h3>
					</div>

					<div class="card-body pad">
						@if (count($errors) > 0)
							<div class="alert alert-danger">
								@foreach ($errors->all() as $error)
									{{$error}}<br>
								@endforeach
							</div>
						@endif

						<div class="form-group">
							<label for="name">Name: </label>
							<input type="text" name="name" id="name" class="form-control" value="{{old('name', $package->name)}}" required>
						</div>
						<div class="form-group">
							<label for="description">Description: </label>
							<textarea name="description" id="summernote" class="form-control">{{old('description', $package->description)}}</textarea>
						</div>
						<!--
						<div class="form-group">
							<label for="description">URL Slug: </label>
							<input type="text" name="url" class="form-control" value="{{old('url', $package->url)}}"/>
						</div>
					-->
					<div class="row">
						<div class="col-lg-6">
						<div class="form-group">
							<label for="taxclass">Tax Class: </label>
							<select name="tax" class="form-control">
							@foreach($taxclasses as $taxclass)
								@if($taxclass->default)
									@if(old('tax', $package->tax) == $taxclass->id)
									<option value="{{$taxclass->id}}" selected="selected">{{$taxclass->name}}</option>
									@else
									<option value="{{$taxclass->id}}">{{$taxclass->name}}</option>
									@endif
								@endif
							@endforeach
							<option value="0">Nontaxable</option>
							@foreach($taxclasses as $taxclass)
								@if(!$taxclass->default)
									@if(old('tax', $package->tax) == $taxclass->id)
									<option value="{{$taxclass->id}}" selected="selected">{{$taxclass->name}}</option>
									@else
									<option value="{{$taxclass->id}}">{{$taxclass->name}}</option>
									@endif
								@endif
							@endforeach
							</select>
						</div>
					</div>
					<div class="col-lg-6">
					<div class="form-group">
						<label for="featured">Out Of Stock: </label>
						<select id="outofstock" name="outofstock" class="form-control">
							<option value="N" {{ $package->is_outofstock ? '' : 'selected' }}>No</option>
							<option value="Y" {{ $package->is_outofstock ? 'selected' : '' }}>Yes</option>
						</select>
					</div>
				</div>
				</div>
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label for="prorate">Prorate: </label>
							<select id="prorate" name="prorate" class="form-control">
								<option value="N" {{ $package->prorate ? '' : 'selected' }}>No</option>
								<option value="Y" {{ $package->prorate ? 'selected' : '' }}>Yes</option>
							</select>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<label for="trial">Free Trial: </label>
							<div class="input-group">
								<input type="number" name="trial" id="trial" min="0" class="form-control" placeholder="0" value="{{old('trial', $package->trial)}}">
								<div class="input-group-append">
									<span class="input-group-text">Days<span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
				<div class="col-lg-6">
				<div class="form-group">
					<label for="featured">Featured: </label>
					<select id="featured" name="featured" class="form-control">
						<option value="N" {{ $package->is_featured ? '' : 'selected' }}>No</option>
						<option value="Y" {{ $package->is_featured ? 'selected' : '' }}>Yes</option>
					</select>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="form-group">
					<label for="exclude">Exclude From API: </label>
					<select id="exclude" name="exclude" class="form-control">
						<option value="N" {{ $package->exclude_from_api ? '' : 'selected' }}>No</option>
						<option value="Y" {{ $package->exclude_from_api ? 'selected' : '' }}>Yes</option>
					</select>
				</div>
			</div>
		</div>
						<div class="form-group">
							<label for="files">Downloadable Files: </label>
							@foreach ($package->files as $file)
								<div class="custom-file>
									<input type="file" name="keep[]" class="custom-file-input" checked value="{{$file->id}}">
									<label>{{$file->filename}}</label>
								</div>
							@endforeach
							<input type="file" name="files[]" id="files" multiple><br>
							Please limit your uploads to 100 MB. If you need to upload bigger files please upgrade <a href="">here</a>.
						</div>
					</div>
				</div>
				<div class="row">
				<div class="col-md-12">
					<div class="card">
							<div class="card-header">
								<h3 class="card-title">
									Product Images
								</h3>
						</div>
						@foreach ($package->images as $image)
						<div id="div_package_image_{{ $image->id }}" class="card-body">
							<img src="{{ config('app.CDN') }}{{ $image->path }}" height="200px" width="200px">
							</img>
							<button type="button" class="btn-danger" onclick="DeleteImage('{{ $image->id }}')">Delete</button>
						</div>
						@endforeach
						<div class="card-body">
							<input type="file" name="product_images[]" id="product_images" multiple><br>
						</div>
					</div>
				</div>
			</div>
		</div>

			<div class="col-md-6">
				<div class="card">
						<div class="card-header">
							<h3 class="card-title">
								Cycles
							</h3>
					</div>

					<div class="card-body" id="cycles">
						{{-- this is handled by javascript below --}}
					</div>

					<div class="card-footer">
						<div class="form-group">
							<a href="#" class="addCycle"> <i class="fa fa-plus"></i> Add Another Cycle</a>
						</div>
					</div>
				</div>

				<div class="card">
						<div class="card-header">
							<h3 class="card-title">
								Options
							</h3>
					</div>
					<div class="card-body">
						<strong>
							<div id="options">
							@foreach ($selectedOptions as $option)
								<span id="option5">
									<input type="hidden" name="options[]" value="{{ $option->id }}">
									<i onclick="$(this).parent().remove()" class="text-danger fas fa-times">
									</i> {{ $option->display_name }}<p>
								</span>
							@endforeach
							</div>
						</strong>
						<select id="selOption" class="form-control">
						@foreach($options as $option)
							<option value="{{ $option->id }}">{{ $option->internal_name }}</option>
						@endforeach
						</select>
					</div>

					<div class="card-footer">
						<div class="form-group">
							<a href="#" id="addOption"> <i class="fa fa-plus"></i> Add Selected Option</a>
						</div>
					</div>
				</div>

				<div class="card">
						<div class="card-header">
							<h3 class="card-title">
								Integration
							</h3>
					</div>
					<div class="card-body">
						@if ($domainIntegrationEnabled)
							<div class="form-group">
								<div class="checkbox">
									<label>
										<input type="checkbox" name="domainIntegration" value="1" {{ $package->domainIntegration === true ? 'checked' : '' }}>
										Enable Domain Integrations
									</label>
								</div>
							</div>
						@endif

						<div class="form-group">
							<select id="integration" name="integration" class="form-control">
								<option></option>
								@foreach($integrations as $integration)
									<option value="{{$integration['shortname']}}" {{ $package->integration !== $integration['shortname'] ?: 'selected'}}>
										{{$integration['title']}}
									</option>
								@endforeach
							</select>
						</div>

						<div id="integration-form">
							{{--<div class="form-group">--}}
	    						{{--<label>DirectAdmin Servers</label>--}}
								{{--<select class="form-control">--}}
									{{--<option><strong>Group: Resllers</strong></option>--}}
									{{--<option>Server 1</option>--}}
									{{--<option>Server 2</option>--}}
									{{--<option><strong>Group: Shared</strong></option>--}}
									{{--<option>Server 1</option>--}}
									{{--<option>Server 3</option>--}}
								{{--</select>--}}
							{{--</div>--}}
						</div>

						<div id="package-form">
						</div>

					</div>
				</div>
		</div>
	</div>

		<a href="/orders"><button type="button" class="btn btn-default mb-3"><i class="fa fa-arrow-circle-o-left"></i> Return</button</a>
		<button type="submit" class="btn btn-success float-right mb-3"><i class="fa fa-plus"></i> Save Package</button>
	</form>
@stop

@section ('javascript')
	<script id="cycle-template" type="text/x-handlebars-template">
		<div class="cycle">
			<div class="float-right removeTools">
				<a href="#" class="removeCycle"><i class="fas fa-trash"></i></a>
			</div>
			<input type="hidden" name="cycle[id][]" value="@{{#if id}}@{{ id }}@{{else}}new@{{/if}}">
			<div class="form-group">
				<label for="price">Price: </label>
				<input type="number" name="cycle[price][]" id="price" class="form-control" min="0" step="0.01" value="@{{ price }}" placeholder="0.00">
			</div>
			<div class="form-group">
				<label for="setup">Setup Fee: </label>
				<input type="number" name="cycle[setup][]" id="setup" class="form-control" min="0" step="0.01" value="@{{ setup }}" placeholder="0.00">
			</div>
			<div class="form-group">
				<label for="cycle">Cycle: </label>
				<select name="cycle[cycle][]" id="cycle" class="form-control">
					<option value="1" @{{#if (cond cycle "===" '1') }}selected@{{/if}}>One-Off</option>
					<option value="2" @{{#if (cond cycle "===" '2') }}selected@{{/if}}>Daily</option>
					<option value="3" @{{#if (cond cycle "===" '3') }}selected@{{/if}}>Weekly</option>
					<option value="4" @{{#if (cond cycle "===" '4') }}selected@{{/if}}>Fortnightly</option>
					<option value="5" @{{#if (cond cycle "===" '5') }}selected@{{/if}}>Monthly</option>
					<option value="6" @{{#if (cond cycle "===" '6') }}selected@{{/if}}>Every 2 Months</option>
					<option value="7" @{{#if (cond cycle "===" '7') }}selected@{{/if}}>Every 3 Months</option>
					<option value="8" @{{#if (cond cycle "===" '8') }}selected@{{/if}}>Every 4 Months</option>
					<option value="9" @{{#if (cond cycle "===" '9') }}selected@{{/if}}>Every 5 Months</option>
					<option value="10" @{{#if (cond cycle "===" '10') }}selected@{{/if}}>Every 6 Months</option>
					<option value="11" @{{#if (cond cycle "===" '11') }}selected@{{/if}}>Every 7 Months</option>
					<option value="12" @{{#if (cond cycle "===" '12') }}selected@{{/if}}>Every 8 Months</option>
					<option value="13" @{{#if (cond cycle "===" '13') }}selected@{{/if}}>Every 9 Months</option>
					<option value="14" @{{#if (cond cycle "===" '14') }}selected@{{/if}}>Every 10 Months</option>
					<option value="15" @{{#if (cond cycle "===" '15') }}selected@{{/if}}>Every 11 Months</option>
					<option value="16" @{{#if (cond cycle "===" '16') }}selected@{{/if}}>Every 12 Months</option>
					<option value="17" @{{#if (cond cycle "===" '17') }}selected@{{/if}}>Every 24 Months</option>
					<option value="18" @{{#if (cond cycle "===" '18') }}selected@{{/if}}>Every 36 Months</option>
				</select>
			</div>
		</div>
	</script>

	<script>
	$(document).ready(function() {
	  $('#summernote').summernote();
	});
	</script>

	<script>
		$(document).on('click', '.addCycle', addCycle);

		function addCycle(values) {
			if (values === undefined) {
				values = {};
			}

			var $html = Handlebars.compile($('#cycle-template').html())(values);
			$('#cycles').append($html);

			return false;
		}

		$(document).on('click', '.removeCycle', function() {
			$(this).parents('.cycle').remove();

			return false;
		});

		$(document).on('click', '#addOption', function() {
			var optId = $('#selOption').val();
			if(optId && optId > 0)
			{
				if(!$('#option'+optId).length){
					$('#options').append('<span id="option'+optId+'"><input type="hidden" name="options[]" value="'+optId+'"/><i onclick="$(this).parent().remove()" class="text-danger fas fa-times"></i>'+$('#selOption').find('option[value='+optId+']').text()+'</span>');
				}
			}
			return false;
		});

		$('.fileChecks').each(function() {
			var self = $(this),
				label = self.next(),
				label_text = label.text();

			label.remove();
			self.iCheck({
				checkboxClass: 'icheckbox_line-blue',
				insert: '<div class="icheck_line-icon"></div>' + label_text
			});
		});

		$('.fileChecks').on('ifChecked', function() {
			$(this).parent().removeClass('icheckbox_line-red').addClass('icheckbox_line-blue');
		});

		$('.fileChecks').on('ifUnchecked', function() {
			$(this).parent().removeClass('icheckbox_line-blue').addClass('icheckbox_line-red');
		});

		/* jshint ignore:start */
		{{-- Logic to handle Package Cycles --}}
		@if (old('cycle.id'))
			@foreach (old('cycle.id') as $k => $id)
				addCycle({id: '{{ $id }}', price: '{{ old('cycle.price')[$k] }}', setup: '{{ old('cycle.setup')[$k] }}', cycle: '{{ old('cycle.cycle')[$k] }}'});
			@endforeach
		@elseif (count($package->cycles))
			@foreach ($package->cycles as $cycle)
				addCycle({id: '{{ $cycle->id }}', price: '{{ $cycle->price }}', setup: '{{ $cycle->fee }}', cycle: '{{ $cycle->cycle }}'});
			@endforeach
		@else
			addCycle();
		@endif
		/* jshint ignore:end */
	</script>

	<script>
		(function($) {
			var settings = {!! $package->settings->groupBy('name')->toJson() !!}; // jshint ignore:line
			var package_id = '{{ $package->id }}';

			$('#integration').on('change', updateIntegration);
			updateIntegration();

			function updateIntegration()
			{
				var integration = $('#integration').val();

				// Disable the submit button so they can't save until the integration form has finished
				$('.btn-success').prop('disabled', true);

				if (integration === '') {
					// Clear the form if they didn't select an integration.
					$('#integration-form').html('');
					$('#package-form').html('');
					$('.btn-success').prop('disabled', false);
				} else {
					// Pull the integration form.
					$.post('/orders/integration/' + integration, {package: package_id, data: integration}, function(data, textStatus, xhr) {
						console.log(data);
					    if (typeof data === 'object' && data.hasOwnProperty('data')) {
							let options = '';
							data.data.forEach(function (item) {
								var name = '';
								if(item.name == null) name = item.hostname;
								else name = item.name;
								options += '<option value="'+item.id+'">'+name+'</option>';
                            });

                const selectForm = $('#integration-form');
                selectForm.append('<div class="form-group"><label>' + integration + ' Servers</label><select class="form-control" name="' + integration + '_server" id="' + integration + '_server" onchange="updatePackage(\'' + $('#integration').val() + '\')">'+options+'</select></div>');
                $('.btn-success').prop('disabled', false);

								@foreach($package_settings as $package_setting)
									@if($package_setting->name == 'directadmin.server' || $package_setting->name == 'directadmin.server_group')
		                   $('#directadmin_server').val('{{ $package_setting->value }}');
									@endif
									@if($package_setting->name == 'cpanel.server')
		                   $('#cpanel_server').val('{{ $package_setting->value }}');
									@endif
								@endforeach

								$('#cpanel_server').on('change', updatePackage(integration));
								updatePackage(integration);
						} else {
                            $('#integration-form').html(data);
                            $('.btn-success').prop('disabled', false);
                            $('#directadmin_server').on('change', updatePackage(integration));
                            $('#cpanel_server').on('change', updatePackage(integration));

							@foreach($package_settings as $package_setting)
								@if($package_setting->name == 'directadmin.server' || $package_setting->name == 'directadmin.server_group')
	                   $('#directadmin_server').val('{{ $package_setting->value }}');
								@endif
								@if($package_setting->name == 'cpanel.server')
	                   $('#cpanel_server').val('{{ $package_setting->value }}');
								@endif
							@endforeach

							$('#cpanel_server').on('change', updatePackage(integration));
							updatePackage(integration);
						}
					});
				}
			}

		}(jQuery));

		function updatePackage(integration)
		{
			var package_id = '{{ $package->id }}';
			var integration = $('#integration').val();
			var integration_id = $('#' + integration + '_server').val();

			// Disable the submit button so they can't save until the integration form has finished
			$('.btn-success').prop('disabled', true);

			if (integration_id === '') {
				// Clear the form if they didn't select an integration.
				$('#package-form').html('');
				$('.btn-success').prop('disabled', false);
			} else {
				// Pull the package form.
				$.post('/orders/integration/' + integration, {package: package_id, integration_id: integration_id}, function(data, textStatus, xhr) {
					console.log(data);
					$('#package-form').html(data);
					$('.btn-success').prop('disabled', false);
					@foreach($package_settings as $package_setting)
							@if($package_setting->name == 'directadmin.package')
									$('#directadmin_package').val('{{ $package_setting->value }}');
							@endif
							@if($package_setting->name == 'cpanel.package')
									 $('#cpanel_package').val('{{ $package_setting->value }}');
							@endif
					@endforeach
				})
				.fail(function() {
						$('.btn-success').prop('disabled', false);
						$('#package-form').html('');
				 });
			}
		}

		function DeleteImage(id)
		{
				$.post('/orders/delete_image', {id: id}, function(data, textStatus, xhr) {
					const package = $('#div_package_image_' + id);
					console.log(package);
						if (package != null) {
						package.remove();
					}
				})
				.fail(function() {
						alert('delete failed.')
				 });
		}
	</script>
@stop
