@extends('Common.template')

@section('title', ' Package Options')
@section('page.title', 'Package Options')
@section('page.subtitle')
	Editor
@stop

@section('breadcrumbs')
	<li class="active">Package Options</li>
@stop

@section ('content')

@if (session('status'))
		<div class="alert alert-success">
				{{ session('status') }}
		</div>
@endif

		@forelse($options as $option)
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">
							{{ $option->display_name }}
						</h3>
						<div class="float-right card-tools">
							<a href="javascript:;">
								<button class="editOption btn btn-primary">
									<i class="fa fa-edit" data-target="{{ $option->id }}"></i>
								</button>
							</a>
							<a href="/orders/options/delete?type=option&id={{ $option->id }}">
								<button class="deleteOption btn btn-primary" data-target="{{ $option->id }}">
									<i class="fas fa-trash"></i>
								</button>
							</a>
						</div>
					</div>
					<div class="card-body p-0">
						<table class="table table-hover table-striped">
							<thead>
								<tr>
									<td class="col-lg-12"></td>
									<td class="col-xs-1"></td>
									<td class="col-xs-1"></td>
								</tr>
							</thead>
							<tbody>
								@forelse ($option->values as $value)
									<tr id="value{{$option->id}}_{{ $value->id }}" class="objChoice">
										<td>{{ $value->display_name }}</td>
										<td>
											<a href="javascript:;">
												<button class="editChoice btn btn-default" data-option="{{ $option->id }}" data-target="{{ $value->id }}"><i class="fa fa-edit"></i></button>
											</a>
										</td>
										<td>
											<a href="/orders/options/delete?type=value&id={{ $value->id }}">
												<button class="deleteChoice btn btn-default" data-option="{{ $option->id }}" data-target="{{ $value->id }}"><i class="fas fa-trash"></i></button>
											</a>
										</td>
									</tr>
								@empty
									<tr>
										<td colspan="4">No option values exist.</td>
									</tr>
								@endforelse
							</tbody>
						</table>
					</div>

					<div class="card-footer no-border">
						<a href="javascript:;" class="addChoice" data-option="{{ $option->id }}"><i class="fa fa-plus"></i> Add Choice</a>
					</div>
				</div>
		@empty
			<tr>
				<td>No options exist.</td>
			</tr>
		@endforelse


			<div class="float-right">
				<a href="javascript:;" class="addOption"><button class="btn btn-primary"><i class="fa fa-plus"></i> Add Option</button></a>
			</div>

	<div class="modal fade" id="mdlEditOptions" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Edit Option</h4>
					<button type="button" class="close" data-dismiss="modal">×</button>
				</div>
				<div class="modal-body">
				<div class="alert alert-danger modal-errors" style="display:none;"></div>
					<form id="frmEditOptions" class="">
						<input type="hidden" name="optionId" value=""/>
						<div class="row">
							<div class="col-sm-6">
								<strong>Internal Name</strong>
							</div>
							<div class="col-sm-6">
								<input type="text" class="internal" name="internal"/>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<strong>Display Name</strong>
							</div>
							<div class="col-sm-6">
								<input type="text" class="display" name="display"/>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<strong>Field Type</strong>
							</div>
							<div class="col-sm-6">
								<select name="type">
									<option value="0">Select Dropdown</option>
									<option value="1"></option>
									<option value="2"></option>
									<option value="3"></option>
									<option value="4"></option>
									<option value="5"></option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<strong>Required</strong>
							</div>
							<div class="col-sm-6">
								<input type="checkbox" class="required" name="required"/>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					<button type="button" id="btnEditOptionsSave" class="btn btn-default">Save</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="mdlEditOptionValues" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Edit Choice</h4>
					<div class="errors"></div>
				</div>
				<div class="modal-body">
					<form id="frmEditOptionValues">
						<input type="hidden" name="optionId" value=""/>
						<input type="hidden" name="valueId" value=""/>
						<div class="row">
							<div class="col-xs-12">
								<div class="row">
									<div class="col-xs-12">
										<strong>Display Name</strong>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-12">
										<input type="text" class="display" name="display"/>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<div class="row">
									<div class="col-xs-12">
										<strong>Price</strong>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-12">
										<input type="text" class="price" name="price"/>
										<select class="cycle" name="cycle">
											<option value="1">One-Off</option>
											<option value="2">Daily</option>
											<option value="3">Weekly</option>
											<option value="4">Fortnightly</option>
											<option value="5">Monthly</option>
											<option value="6">Every 2 Months</option>
											<option value="7">Every 3 Months</option>
											<option value="8">Every 4 Months</option>
											<option value="9">Every 5 Months</option>
											<option value="10">Every 6 Months</option>
											<option value="11">Every 7 Months</option>
											<option value="12">Every 8 Months</option>
											<option value="13">Every 9 Months</option>
											<option value="14">Every 10 Months</option>
											<option value="15">Every 11 Months</option>
											<option value="16">Every 12 Months</option>
											<option value="17">Every 24 Months</option>
											<option value="18">Every 36 Months</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="row">
									<div class="col-xs-12">
										<strong>Setup Fee</strong>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-12">
										<input type="text" class="fee" name="fee"/>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					<button type="button" id="btnEditOptionValuesSave" class="btn btn-default">Save</button>
				</div>
			</div>

		</div>
	</div>

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
					<form method="post" action="#" id="confirmForm">
						<input type="hidden" name="_token" value="{{csrf_token()}}">
						<button id="btnYes" type="submit" class="btn btn-success btn-lrg">Yes</button>
					</form>
				</div>
			</div>
		</div>
	</div>

@stop
@section ('javascript')
<script type="text/javascript">
	function getOptionData(id)
	{
		$('.errors').empty();
		$('.animationLoading').css('display','block');
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/orders/options/get/option/'+id,
			type: 'GET'
		})
		.done(function(data){displayOptionForm(data);})
		.fail(function(data) {
			displayErrors(data.errors);
			$('.errors').attr('style', '');
		});
	}
	function getOptionChoiceData(id,id2)
	{
		$('.errors').empty();
		$('.animationLoading').css('display','block');
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/orders/options/get/value/'+id+'/'+id2,
			type: 'GET'
		})
		.done(function(data){displayChoiceForm(data);})
		.fail(function(data) {
			displayErrors(data.errors);
			$('.errors').attr('style', '');
		});
	}
	function displayOptionForm(data)
	{
		$('#frmEditOptions').html(data);
		$('.animationLoading').css('display','none');
		$('#mdlEditOptions').modal('show');
		$('.modal-errors').html('');
		$('.modal-errors').attr('style', 'display:none;');
	}
	function getChoiceData(id)
	{
		$('.errors').empty();
		$('.animationLoading').css('display','block');
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/orders/options/get/value/'+id,
			type: 'GET'
		})
		.done(displayChoiceForm(data))
		.fail(function(data) {
			displayErrors(data.errors);
			$('.errors').attr('style', '');
		});
	}
	function displayChoiceForm(data)
	{
		$('#frmEditOptionValues').html(data);
		$('.animationLoading').css('display','none');
		$('#mdlEditOptionValues').modal('show');
	}
	function addOption(e)
	{
		var optionId = 'new';
		getOptionData(optionId);
	}
	function editOption(e)
	{
		var optionId = $(e).find('i').data('target');
		getOptionData(optionId);
	}
	function deleteOption(e)
	{
		$('.errors').empty();
		$('.animationLoading').css('display','block');
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/orders/options/delete',
			type: 'POST',
			data: 'type=option&id='+$(e).data('target')
		})
		.done(function(data)
		{
				window.location.reload();
		})
		.fail(function(data) {
			displayErrors(data.errors);
			$('.errors').attr('style', '');
		});
	}
	function saveOption(e)
	{
		$('.errors').empty();
		$('.animationLoading').css('display','block');
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/orders/options/save',
			type: 'POST',
			data: $('#frmEditOptions').serialize()+'&type=option'
		})
		.done(function(data)
		{
			if(data.status)
			{
				window.location.reload();
			}
			else
			{
				displayErrors(data.errors);
			}
			$('.animationLoading').css('display','none');
		})
		.fail(function(data) {
			var errors = data.responseJSON.errors;
			var errorMsg = '';
			$.each(errors,function(k,v){
					var errorKey = errors[k];
					for(var x = 0; x < errorKey.length; x++)
					{
							if(errorMsg != '') errorMsg += '<br>';
							errorMsg += errorKey[x];
					}
			});

			$('.modal-errors').html(errorMsg);
			$('.modal-errors').attr('style', '');
		});
	}
	function addChoice(e)
	{
		var optionId = $(e).data('option');
		getOptionChoiceData(optionId,'new');
	}
	function editChoice(e)
	{
		var optionId = $(e).data('option');
		var choiceId = $(e).data('target');
		getOptionChoiceData(optionId,choiceId);
	}
	function deleteChoice(e)
	{
		$('.errors').empty();
		$('.animationLoading').css('display','block');
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/orders/options/delete',
			type: 'POST',
			data: 'type=value&id='+$(e).data('target')
		})
		.done(function(data)
		{
				window.location.reload();
		})
		.fail(function(data) {
			displayErrors(data.errors);
			$('.errors').attr('style', '');
		});
	}
	function saveChoice(e)
	{
		$('.errors').empty();
		$('.animationLoading').css('display','block');
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/orders/options/save',
			type: 'POST',
			data: $('#frmEditOptionValues').serialize()+'&type=value'
		})
		.done(function(data)
		{
			if(data.status == 1)
			{
				window.location.reload();
			}
			else
			{
				displayErrors(data.errors);
			}
			$('.animationLoading').css('display','none');
		})
		.fail(function(data) {
			$('.animationLoading').css('display','none');
			displayErrors(data.responseJSON.errors);
		});
	}

	function displayErrors(data)
	{
		$.each(data,function(k,v){
			$('.errors').append('<p>'+v+'</p>');
			$('.modal-errors').append('<p>'+v+'</p>');

			$.each(v['inputs'],function(k2,v2){
				$('[name='+v['v2']+']').addClass('text-danger','errored');
			});
		});
	}

	$(document).on("click",".addOption",function(){addOption(this);});
	$(document).on("click",".editOption",function(){editOption(this);});
	$(document).on("click",".deleteOption",function(){deleteOption(this);});
	$(document).on("click",".addChoice",function(){addChoice(this);});
	$(document).on("click",".editChoice",function(){editChoice(this);});
	//$(document).on("click",".deleteChoice",function(){
	$('a:has(i.fa-trash)').click(function(e) {
		$('#confirm').find('#deleteText').text($(this).parent().siblings('.box-title,td:nth-child(1)').text());
		$('#confirm').find('#confirmForm').attr('action', $(this).attr('href'));
		$('#confirm').modal('show');

		return false;
	});
	$(document).on("click","#btnEditOptionValuesSave",function(){saveChoice(this);});
	$(document).on("click","#btnEditOptionsSave",function(){saveOption(this);});
</script>
@stop
