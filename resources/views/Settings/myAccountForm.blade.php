@extends ('Common.template')

@section('title', ' My Account - Edit Account Information')

@section('page.title', 'My Account')
@section('page.subtitle', 'Edit Account Information')

@section('breadcrumbs')
  <a href="/settings/my-account">My Account</a>
  <li class="breadcrumb-item active">Edit Account</li>
@stop

@section('content')
	@if (count($errors) > 0)
		<div class="alert alert-dismissible alert-danger">
			<button type="button" class="close" data-dismiss="alert">×</button>
			@foreach ($errors->all() as $error)
				{{$error}}<br>
			@endforeach
		</div>
	@endif
	<form method="post" action="{{ $user->isCustomer() ? '/settings/myaccount/edit' : '/settings/my-account/edit' }}" enctype="multipart/form-data" autocomplete="off">
		<input type="hidden" name="_token" value="{{csrf_token()}}">
		<div class="row">
			<div class="col-lg-8 col-md-12 col-sm-12">
				<div class="row">
					@if(!$user->isCustomer())
						<div class="col-lg-6 col-md-12 col-sm-12">
					@else
						<div class="col-sm-12">
					@endif
						<div class="box">
							<div class="box-header">
								<h3 class="box-title">Account Information</h3>
							</div>
							<div class="box-body">
								<div class="form-group">
									<label for="username">Username: <span class="text-danger">*</span></label>
									<input type="text" name="username" value="{{ $user->username }}" class="form-control">
								</div>
								<div class="form-group">
									<label for="password">Password:</label>
									<input type="password" name="password" class="form-control" placeholder="Leave blank for no change.">
								</div>
								<div class="form-group">
									<label for="password-confirmation">Password Confirmation:</label>
									<input type="password" name="password_confirmation" class="form-control" placeholder="Leave blank for no change.">
								</div>
								@if($user->isCustomer())
									<button type="submit" class="btn btn-success float-right">Save</button>
								@endif
							</div>
						</div>
					</div>
					@if(!$user->isCustomer())
					<div class="col-lg-6 col-md-12 col-sm-12">
						<div class="box">
							<div class="box-header">
								<h3 class="box-title">Site Settings</h3>
							</div>
							<div class="box-body">
								<div class="form-group">
									<label for="site-name">Site Name</label>
									<input type="text" name="site-name" value="{{ old('site-name', $user->siteSettings('name')) }}" class="form-control">
								</div>
								<div class="form-group">
									<label for="site-logo">Site Logo:</label>
									<div>
										@if ($user->siteSettings('logo'))
											<img id="logo-img" src="{{config('app.CDN')}}{{$user->siteSettings('logo')}}" width="250">
										@endif
									</div>
									<input type="file" accept="image/*" name="site-logo" id="site-logo" class="form-control">
								</div>
								<button type="submit" class="btn btn-success float-right">Save</button>
							</div>
						</div>
					</div>
					@endif
				</div>
			</div>
			<div class="col-lg-4 col-md-6 col-sm-12">
				<div class="box">
					<div class="box-header">
						<h3 class="box-title">Security Settings</h3>
					</div>
					<div class="box-body">
						<div class="form-group checkbox">
							<label for="2fa">
								<input type="checkbox" name="2fa" id="2fa" value="Y" {!! $user->has2fa() ? 'checked data-verified="true"' : '' !!}>
								Enable Two-Factor Authenication (2FA)
							</label>
						</div>
					</div>
				</div>
			</div>
			</div>
		</div>
	</form>

	<div class="modal fade" id="2faModal">
		<form id="2fa-form">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">2FA Activation</h4>
            <button type="button" class="close" data-dismiss="modal">×</button>
					</div>
					<div class="modal-body">
						<div class="text-center">
							<div class="form-group">
								<button id="qr" type="button" class="btn btn-default btn-lrg">
									<i class="fa fa-spin fa-refresh"></i>
									Loading QR Code
								</button>
							</div>

							<div class="form-group center-block" style="width: 200px" id="2faGroup">
								<label for="2faverify"></label>
								<input type="text" id="2faverify" name="2faverify" placeholder="2FA Code" class="form-control">
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default float-left btn-lrg" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-success btn-lrg" disabled>&nbsp; Verify Code</button>
					</div>
				</div>
			</div>
		</form>
	</div>
@stop

@section('javascript')
	<script>
		$('#2fa').on('change', function() {
			if ($(this).prop('checked')) {
				$('#2faModal').modal('show');
			} else {
				$('#2fa').parents('.form-group').removeClass('has-error');
				$('#2fa').siblings('span').remove();
				$('#2fa').data('verified', false);

				$.ajax({
					headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
					@if($user->isCustomer())
					url: '/settings/myaccount/edit',
					@else
					url: '/settings/my-account/edit',
					@endif
					type: 'POST',
					dataType: 'json',
					data: {action: 'remove2fa'},
				})
				.error(function() {
					$('#2fa').parents('.form-group').addClass('has-error');
					$('#2fa').parents('label').append($('<span>').text(' - Error Disabling'));
					$('#2fa').prop('checked', true);
					$('#2fa').data('verified', true);
				});
			}
		});

		$('#2faModal').on('hidden.bs.modal', function() {
			if ($('#2fa').data('verified') !== true) {
				$('#2fa').prop('checked', false);
			}
		});

		$('#2faModal').on('shown.bs.modal', function() {
			if ($('#qr').length !== 0) {
				$.ajax({
					headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
					@if($user->isCustomer())
					url: '/settings/myaccount/edit',
					@else
					url: '/settings/my-account/edit',
					@endif
					type: 'POST',
					dataType: 'json',
					data: {action: 'get2fa'},
				})
				.done(function(data) {
					$('#qr').replaceWith(data.QR);
					$('#2faModal button[type="submit"]')
						.prop('disabled', false);
				})
				.error(function() {
					var $alert = $('<div>')
						.addClass('alert alert-danger')
						.text('Unable to load QR Code for 2FA Activation.');

					$('#2faModal .modal-body')
						.html($alert);
				});
			}
		});

		$('#2fa-form').on('submit', function(e) {
			$('#2faGroup').removeClass('has-error');
			$('#2faGroup label').text('');
			$('#2fa-form button[type="submit"]')
				.prepend($('<i>').addClass('fa fa-spin fa-refresh'));

			$.ajax({
				headers: { 'X-CSRF-Token': '{{csrf_token()}}' },
				@if($user->isCustomer())
				url: '/settings/myaccount/edit',
				@else
				url: '/settings/my-account/edit',
				@endif
				type: 'POST',
				dataType: 'json',
				data: $('#2fa-form').serialize() + '&action=verify2fa',
			})
			.done(function(data) {
				$('#2fa-form button[type="submit"] i').remove();
				if (data.valid) {
					$('#2faModal').modal('hide');
					$('#2fa').data('verified', true);
				} else {
					$('#2fa').data('verified', false);
					$('#2faGroup').addClass('has-error');
					$('#2faGroup label').text('Invalid 2FA Code');
				}
			});

			return false;
		});

		var logoImg = document.getElementById('logo-img');
		var siteLogo = document.getElementById('site-logo');
		siteLogo.addEventListener('change', function (e) {
			readImageFile(e.target.files[0], function (ev) {
			    logoImg.setAttribute('src', ev.target.result);
            });
        });

        var readImageFile = (file, callback) => {
            if (file) {
                const reader = new FileReader();
                reader.onload = callback;
                reader.readAsDataURL(file);
            }
        };
	</script>
@stop
