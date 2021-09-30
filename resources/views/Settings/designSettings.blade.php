@extends ('Common.template')

@section('title', ' Design Settings')

@section('page.title', 'Design Settings')
@section('page.subtitle', '')

@section('breadcrumbs')
	<li>Settings</li>
	<li class="active">Design Settings</li>
@stop

@section('content')
<form id="frmDesignSettings">
	<div class="box">
		<div class="box-body">
			<div class="row">
				<div class="col-xs-6">
					<h4>Invoices</h4>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-6">
					<label>Color</label>
					<select class="form-control" name="invoiceColor">
						<option @if($invoiceColor && $invoiceColor == 0) selected @endif value="0">Blue</option>
						<option @if($invoiceColor && $invoiceColor == 1) selected @endif value="1">Red</option>
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<h4>Frontend</h4>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<label>Custom Header HTML:</label>
					<textarea class="form-control" rows="8" name="headerHTML">{!! $customHeader !!}</textarea>
				</div>
				<div class="col-md-12">
					<label>Custom Footer HTML:</label>
					<textarea class="form-control" rows="8" name="footerHTML">{!! $customFooter !!}</textarea>
				</div>
				<div class="col-xs-12">
					<label>Custom CSS:</label>
					<textarea type="form-control" rows="8" class="form-control" name="customCSS">{!! $customCSS !!}</textarea>
				</div>
			</div>
		</div>
		<div class="box-footer">
			<button type="button" class="btn btn-success float-right" id="frmDesignSettingsBtnSave">
				<i class="fa fa-disk"></i>
				<span>Save</span>
			</button>
		</div>
	</div>
</form>
@stop

@section('javascript')
<script type="text/javascript">
	$('#frmDesignSettingsBtnSave').click(function(){
		$('#frmDesignSettingsBtnSave').find('i').removeClass('fa-disk').addClass('fa-spin fa-refresh');
		$('#frmDesignSettingsBtnSave').removeClass('brn-success').addClass('btn-danger').prop('disabled', true);
		$('#frmDesignSettingsBtnSave').find('span').text('Saving...');
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/settings/design-settings',
			type: 'POST',
			dataType: 'json',
			data: $('#frmDesignSettings').serialize()
		})
		.success(function(data) {
			$('#frmDesignSettingsBtnSave').find('i').addClass('fa-disk').removeClass('fa-spin fa-refresh');
			$('#frmDesignSettingsBtnSave').removeClass('btn-danger').addClass('btn-success').prop('disabled', false);
			$('#frmDesignSettingsBtnSave').find('span').text('Success').delay(2000).text('Save');
		})
		.fail(function(data) {
			var errors = data.responseJSON;

			var html = '<ul>';

			$.each(errors, function(index, val) {
				html = html + '<li>' + val + '</li>';
			});
			$('#errors').html(errors + '</ul>');
			$('#frmDesignSettingsBtnSave').find('i').addClass('fa-disk').removeClass('fa-spin fa-refresh');
			$('#frmDesignSettingsBtnSave').removeClass('btn-danger').addClass('btn-success').prop('disabled', false);
			$('#frmDesignSettingsBtnSave').find('span').text('Error').delay(2000).text('Save');
		});
	});
</script>
@stop
