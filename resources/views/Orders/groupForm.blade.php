@extends('Common.template')

@section('title', ' Packages')
@section('page.title', 'Packages')

@section('page.subtitle')
	{{$type}} Group
@stop

@section('breadcrumbs')
	<li class="breadcrumb-item"><a href="/orders"> Packages</a></li>
	<li class="breadcrumb-item active">{{$type}} Group</li>
@stop

@section ('content')
	<form method="post">
		<div class="card card-primary">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">

			<div class="card-body">
				@if (count($errors) > 0)
					<div class="alert alert-danger">
						@foreach ($errors->all() as $error)
							{{$error}}<br>
						@endforeach
					</div>
				@endif

				<div class="form-group">
					<label for="name">Name: </label>
					<input type="text" name="name" id="name" class="form-control" value="{{old('name', $group->name)}}">
				</div>
				<div class="form-group">
					<label for="description">Description: </label>
					<textarea name="description" id="summernote" class="form-control">{{old('description', $group->description)}}</textarea>
				</div>
				<div class="form-group">
					<label for="link">URL: </label>
					<div class="input-group">
						<div class="input-group-btn primary">
							<button class="btn btn-success">{{$siteURL}}/order/</button>
						</div>
						<input type="text" name="link" id="link" class="form-control" value="{{old('link', $group->url)}}" placeholder="{{old('name', $group->name)}}" readonly>
					</div>
				</div>
				<div class="form-group">
					<label for="type">Form Type: </label>
					<select class="form-control" id="type" name="type">
						<option value="0" {{ old('type', $group->type) == '0' ?: 'selected' }}>Package Comparison 1</option>
						<option value="1" {{ old('type', $group->type) == '1' ?: 'selected' }}>Package Comparison 2</option>
						<option value="2" {{ old('type', $group->type) == '2' ?: 'selected' }}>Package Comparison 3</option>
					</select>
				</div>
			</div>
		</div>

		<a href="/orders"><button type="button" class="btn btn-default"><i class="fa fa-arrow-circle-o-left"></i> Return</button</a>
		<button type="submit" class="btn btn-primary float-right"><i class="fa fa-plus"></i> Save Group</button>
	</form>
@stop

@section ('javascript')
<script>
$(document).ready(function() {
	$('#summernote').summernote();

	$('#name').bind('change keyup', function() {
    $("#link").val(string_to_slug($("#name").val()));
  });

	function getUrl(text)
	{
			text = text.replace(/["~!@#$%^&*\(\)_+=`{}\[\]\|\\:;'<>,.\/?"\t\r\n]+/g, '');
			text = text.replace(/\s+/g, '-');
			if(text.substring(0, 1) == '-' && (text.length == 1 || text.length == 3)) text = text.replace(/-/g, '');
			text = text.replace(/--/g, '');
			text = text.replace(/---/g, '');
			if(text.substring(0, 1) == '-' && text.length == 1) text = text.replace(/-/g, '');
			return text.toLowerCase();
	}

	function string_to_slug (str)
	{
	    str = str.replace(/^\s+|\s+$/g, ''); // trim
	    str = str.toLowerCase();

	    // remove accents, swap ñ for n, etc
	    var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
	    var to   = "aaaaeeeeiiiioooouuuunc------";
	    for (var i=0, l=from.length ; i<l ; i++) {
	        str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
	    }

	    str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
	        .replace(/\s+/g, '-') // collapse whitespace and replace by -
	        .replace(/-+/g, '-'); // collapse dashes

	    return str;
	}
});
</script>
@stop
