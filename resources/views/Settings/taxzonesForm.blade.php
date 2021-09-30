@extends ('Common.template')

@section('title', ' Tax Settings')

@section('page.title', 'Tax Settings')
@section('page.subtitle', 'Tax Settings')

@section('breadcrumbs')
	<li class="active">{{ trans('backend.tax-welcome') }}</li>
@stop

@section('content')

<div id="div_error" class="alert alert-dismissible alert-danger" style="display:none;">
	<button type="button" class="close" onclick="CloseError();">×</button>
	<span id="span_error"></span>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="card" id="zonesBox">
			<div class="card-header">
				<h3 class="card-title">{{ trans('backend.tax-zones') }}</h3>
			</div>
			<div class="card-body">
				<table id="zoneList" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>{{ trans('backend.tax-zonename') }}</th>
							<th>{{ trans('backend.tax-actions') }}</th>
						</tr>
					</thead>
					<tbody>
					@foreach($zones as $zone)
						<tr>
							<td>{{$zone->name}}</td>
							<td class="card-tools text-center">
								<button onclick="editTaxZone(this);" class="frmZoneBtnEdit btn btn-default" data-target="{{$zone->id}}">
									<i class="fa fa-pencil-square-o"></i> {{ trans('backend.tax-edit') }}
								</button>
								<button onclick="deleteTaxZone(this);" data-target="{{$zone->id}}" class="frmZoneBtnDelete btn btn-default">
									<i class="fa fa-trash-o"></i> {{ trans('backend.tax-delete') }}
								</button>
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
			<div class="card-footer">
				<button onclick="editTaxZone(this);" class="btn btn-success float-right" data-target="-1"><i class="fa fa-plus"></i> {{ trans('backend.tax-addzone') }}</button>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="card" id="classesBox">
			<div class="card-header">
				<h3 class="card-title">{{ trans('backend.tax-classes') }}</h3>
			</div>
			<div class="card-body">
				<table id="classesList" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>{{ trans('backend.tax-class') }}</th>
							<th>{{ trans('backend.tax-actions') }}</th>
							<th>Default</th>
						</tr>
					</thead>
					<tbody>
					@foreach($classes as $taxclass)
						<tr>
							<td>{{$taxclass->name}}</td>
							<td class="card-tools text-center">
								<button onclick="editTaxClass(this);" class="frmClassBtnEdit btn btn-default" data-target="{{$taxclass->id}}">
									<i class="fa fa-pencil-square-o"></i>{{ trans('backend.tax-edit') }}
								</button>
								<button onclick="deleteTaxClass(this);" data-target="{{$taxclass->id}}" class="frmClassBtnDelete btn btn-default">
									<i class="fa fa-trash-o"></i> {{ trans('backend.tax-delete') }}
								</button>
							</td>
							<td>
							@if($taxclass->default == 1)
								Default
							@endif
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
			<div class="card-footer">
				<button onclick="editTaxClass(this);" class="frmClassBtnAdd btn btn-success float-right" data-target="-1"><i class="fa fa-plus"></i> {{ trans('backend.tax-addclass') }}</button>
			</div>
		</div>
	</div>
</div>
<!-- Modal -->
<div class="modal fade" id="EditZone" role="dialog" width="800px">
    <div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">{{ trans('backend.tax-addzoneedit') }}</h4>
				<button type="button" class="close" data-dismiss="modal">×</button>
			</div>
			<div class="modal-body">
				<form id="EditZoneForm">
					<input type="hidden" name="zoneId" value="-1"/>
					<div class="row">
						<div class="col-xs-12">
							<input class="form-control" name="zoneName" type="text" placeholder="{{ trans('backend.tax-zonename') }}"/>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<select class="form-control" id="frmSelectCountry">
							@foreach($countries as $country)
								<option value="{{$country->id}}">{{$country->name}}</option>
							@endforeach
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-6 text-center">
							<button class="btn btn-success" type="button" id="frmCheckAll"><i class="fa fa-check-circle-o"></i> {{ trans('backend.tax-checkall') }}</button>
						</div>
						&nbsp; &nbsp;
						<div class="col-xs-6 text-center">
							<button class="btn btn-success" type="button" id="frmCheckNone"><i class="fa fa-circle-o"></i> {{ trans('backend.tax-checknone') }}</button>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12 tab-content">
							<div class="row" id="countiesList"></div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('backend.tax-cancel') }}</button>
				<button type="button" class="frmZoneBtnSave btn btn-default">{{ trans('backend.tax-save') }}</button>
			</div>
		</div>

    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="EditClass" role="dialog">
    <div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">{{ trans('backend.tax-addclassedit') }}</h4>
			</div>
			<div class="modal-body">
				<form id="EditClassForm">
					<input type="hidden" name="classId" value="-1"/>
					<div class="row">
						<div class="col-xs-12">
							<input class="form-control" name="className" type="text" placeholder="Tax Class Name"/>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<input class="form-control" name="classDefault" value="1" type="checkbox"/> Default
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<table id="EditClassTable" class="table table-border table-hover table-responsive">
								<thead>
									<tr>
										<th class="col-xs-6">{{ trans('backend.tax-addclasszone') }}</th>
										<th class="col-xs-6">{{ trans('backend.tax-addclassrate') }}</th>
									</tr>
								</thead>
								<tbody id="EditClassTableBody">
								</tbody>
							</table>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('backend.tax-cancel') }}</button>
				<button type="button" class="frmClassBtnSave btn btn-default">{{ trans('backend.tax-save') }}</button>
			</div>
		</div>

    </div>
</div>
@stop

@section('javascript')
<script type="text/javascript">
	var oldCountry = 222;

	//edit tax zone
	function editTaxZone(e){
		var zoneId = $(e).data('target');
		$('#EditZone input[name=zoneId]').val(zoneId);
		$('#countiesList').empty().append('<div style="margin:0 auto;" id="frmLoading2" class="fa fa-spinner fa-spin fa-refresh"></div>');
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/settings/invoice-settings/tax-zones/zone',
			type: 'POST',
			data: 'zone='+zoneId
		})
		.done(function(data, textStatus, jqXHR) {
			$('#frmLoading2').remove();
			$('#frmSelectCountry').val('222');
			$('#countiesList').append(data.html);
			$('#EditZoneForm input[name=zoneName]').val(data.name);
			oldCountry = 222;
			//$('#countiesOf222').css('display','block');
			$('#EditZone').modal('show');
		})
		.fail(function(data) {
			$('#frmLoading2').remove();
			alert('Error.');
		});
	}

	$('#frmCheckAll').click(function(){
		$('#countiesOf'+$('#frmSelectCountry').val()+' input[type=checkbox]').prop('checked',true);
	});
	$('#frmCheckNone').click(function(){
		$('#countiesOf'+$('#frmSelectCountry').val()+' input[type=checkbox]').prop('checked',false);
	});

	//save tax zone
	$('.frmZoneBtnSave').click(function(){
		$(this).prepend('<i id="frmLoading2" class="fa fa-spinner fa-spin fa-refresh"></i>');
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/settings/invoice-settings/tax-zones/zone/save',
			type: 'POST',
			data: $('#EditZoneForm').serialize()
		})
		.done(function(data, textStatus, jqXHR) {
			$('#frmLoading2').remove();

			//update mainpage with data returned (changes)
			//data
			$('#zoneList tbody').html(data.zones);
			$('#classesList tbody').html(data.classes);

			$('#EditZone').modal('hide');
		})
		.fail(function(data) {
			$('#frmLoading2').remove();
			alert('Error.');
		});
	});

	//delete tax zone
	function deleteTaxZone(e){
		$(e).find('i').addClass('fa-spin fa-refresh');
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/settings/invoice-settings/tax-zones/zone/delete',
			type: 'POST',
			data: 'zoneId='+$(e).data('target')
		})
		.done(function(data, textStatus, jqXHR) {
			$('.frmZoneBtnDelete').find('i').removeClass('fa-spin fa-refresh');

			//update mainpage with data returned (changes)
			//data
			$('#zoneList tbody').html(data.zones);
			$('#classesList tbody').html(data.classes);
		})
		.fail(function(data) {
			$('.frmZoneBtnDelete').find('i').removeClass('fa-spin fa-refresh');
			alert('Error.');
		});
	}

	//delete tax class
	function deleteTaxClass(e){
		$(e).find('i').addClass('fa-spin fa-refresh');
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/settings/invoice-settings/tax-zones/class/delete',
			type: 'POST',
			data: 'classId='+$(e).data('target')
		})
		.done(function(data, textStatus, jqXHR) {
			$('.frmClassBtnDelete').find('i').removeClass('fa-spin fa-refresh');

			//update mainpage with data returned (changes)
			//data
			$('#zoneList tbody').html(data.zones);
			$('#classesList tbody').html(data.classes);
		})
		.fail(function(data) {
			$('.frmClassBtnDelete').find('i').removeClass('fa-spin fa-refresh');
			alert('Error.');
		});
	}

	//save tax class
	$('.frmClassBtnSave').click(function(){
		$(this).prepend('<i id="frmLoading2" class="fa fa-spinner fa-spin fa-refresh"></i>');
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/settings/invoice-settings/tax-zones/class/save',
			type: 'POST',
			data: $('#EditClassForm').serialize()
		})
		.done(function(data, textStatus, jqXHR) {
			$('#frmLoading2').remove();

			//update mainpage with data returned (changes)
			//data
			$('#zoneList tbody').html(data.zones);
			$('#classesList tbody').html(data.classes);

			$('#EditClass').modal('hide');
		})
		.fail(function(data) {
			$('#frmLoading2').remove();
			alert('Error.');
		});
	});

	//edit tax class
	function editTaxClass(e){
		$('#EditClassForm input[name=classDefault]').prop('checked',false);
		var classId = $(e).data('target');
		$('#EditClass input[name=classId]').val(classId);
		$('#EditClassTableBody').empty().append('<div style="margin:0 auto;" id="frmLoading2" class="fa fa-spinner fa-spin fa-refresh"></div>');
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/settings/invoice-settings/tax-zones/class',
			type: 'POST',
			data: 'class='+classId
		})
		.done(function(data, textStatus, jqXHR) {
			if(data.default == '1')
			{
				$('#EditClassForm input[name=classDefault]').prop('checked',true);
			}
			$('#frmLoading2').remove();
			$('#EditClassTableBody').append(data.html);
			$('#EditClassForm input[name=className]').val(data.name);
			$('#EditClass').modal('show');
		})
		.fail(function(data) {
			$('#frmLoading2').remove();
			//alert('Error.');
			$('#div_error').attr('style', '');
			$('#span_error').html('{{ trans("backend.tax-addclassadderror") }}');
		});
	}

	//tax zone select change
	$('#frmSelectCountry').change(function(){
		$('#countiesList #countiesOf'+oldCountry).css('display','none');
		$('#countiesList').append('<div style="margin:0 auto;" id="frmLoading2" class="fa fa-spinner fa-spin fa-refresh"></div>');
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/settings/invoice-settings/tax-zones/regions',
			type: 'POST',
			data: 'country='+$('#frmSelectCountry').val()
		})
		.done(function(data, textStatus, jqXHR) {
			$('#frmLoading2').remove();
			if(document.getElementById('countiesOf'+$('#frmSelectCountry').val()))
			{
				//$('#countiesOf'+$('#frmSelectCountry').val()).css('display','block');
			}
			else
			{
				$('#countiesList').append(data);
			}
			oldCountry = $('#frmSelectCountry').val();
		})
		.fail(function(data) {
			$('#frmLoading2').remove();
			//$('#btnCreateInvoice').find('i').addClass('fa-pencil-square-o').removeClass('fa-spin fa-refresh');
			//$('#btnCreateInvoice').removeClass('btn-info').addClass('btn-danger').prop('disabled', false);
			//var html = '';
			//$.each(data.responseJSON,function(k,v){
			//	$.each(v,function(k2,v2){
			//		html = html + '<li>' + v2 + '</li>';
			//		console.log(v2);
			//	});
			//})
			//$('#frmCreateInvoice .invoice-info').prepend('<div id="errorAlert" class="alert alert-danger"><strong>Error!</strong><ul>'+html+'</ul></div>');
			//$( "window" ).scrollTop( $('#errorAlert').scrollTop());
		});
	});

	function CloseError()
	{
			$('#div_error').attr('style', 'display:none;');
	}
</script>
@stop

@section('css')
<style>
	#frmCheckAll, #frmCheckNone{
		cursor:pointer;
	}
	#EditZone .row{
		margin-top:10px;
	}
</style>
@stop
