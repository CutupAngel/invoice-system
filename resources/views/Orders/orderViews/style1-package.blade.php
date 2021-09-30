@extends('Common.frontendLayout')
@section('title', 'Checkout')

@section('css')
	<link rel="stylesheet" href="https://v2.b-cdn.uk/dist/css/checkout2.css">
	<style>
	.grey {
		background-color: #373f50;
		text-align: left;
		padding: 12px 0px 0px 12px;
		font-size: xx-small;
		font-weight: 200;
	}
	.grey p {
		font-size: medium !important;
    font-weight: 500 !important;
    margin-bottom: 12px !important;
    color: white;
	}
	.pricing h3 {
		text-align: center;
	}
	.check {
    display: block;
    position: relative;
    padding-left: 35px;
    margin-top: 6px;
    margin-bottom: 12px;
    cursor: pointer;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    font-size: 14px !important;
    font-weight: 400 !important;
	}
	.bottom.center {
    text-align: center;
	}
	.span-switch {
    position: relative;
    bottom: 12px;
	}
	.span-switch {
    position: relative;
    bottom: 12px;
	}
	.order-checkbox {
		height: 12px !important;
		width: auto !important;
		visibility: visible !important;
	}
	</style>
	<link  href="https://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.4/fotorama.css" rel="stylesheet">
@stop

@php
	$url = '';				
	function checkFileExists($package){
		$imageSize = 0;
		foreach ($package->images as $image){
			if (validImage( config('app.CDN') ."". $image->path )) {
				$imageSize = $imageSize + 1;
			}
		}
		if($imageSize > 0) {
			return true;
		}else{
			return false;
		}
	}
	
	function validImage($file) {
	   $size = getimagesize($file);
	   return (strtolower(substr($size['mime'], 0, 5)) == 'image' ? true : false);  
	}
	
	$class_col = 'col-sm-6';
	if(!checkFileExists($package)){
		$class_col = $url;
	}
	
@endphp

@section('content')
<div class="jumbotron">
	<!-- Main content -->
	    <section class="content">
				<form action="{{ route('add-to-cart') }}" method="post">
	      <!-- Default box -->
	      <div class="card card-solid">
	        <div class="card-body">
	          <div class="row">
					
	            <div class="col-12 {{$class_col}} imgLayout">
	              <div class="fotorama">
	    						@foreach ($package->images as $image)
	    					  	<img src="{{ config('app.CDN') }}{{ $image->path }}" />
	    						@endforeach
	    					</div>
	            </div>
	            <div class="col-12 {{$class_col}} text-center">
				
	            <h3><strong>Product Configuration:</strong> {{ $package->name }}</h3>
							<p>This package is <strong>@php if($package->is_outofstock) echo 'Out of Stock'; else echo 'In Stock'; @endphp</strong></p>
							<div class="panel grey"></div>
	            <h4>Select desired options, and continue to checkout.</h4>

							<div class="col-6 text-center">
								<h3><strong>Product Description:</strong></h3>
							<hr>
							{!! $package->description !!}
						</div>

	              <div class="bg-gray py-2 px-3 mt-4">
	              <select class="form-control" name="cycle">
	                @foreach ($package->cycles as $i => $cycle)
	                <option value="{{ $cycle->id }}">
	                  Price:
	                  {!! $currency->symbol !!}{{ number_format($cycle->price / $default_currency->conversion * $currency->conversion, 2) }} {{ $cycle->cycle() }},
	                  Setup:
	                  {!! $currency->symbol !!}{{ number_format($cycle->fee / $default_currency->conversion * $currency->conversion,2) }}
	                </option>
	                @endforeach
	              </select>
	              </div>

	              <div class="pricing h3">
									<h3><strong>Product Options:</strong></h3>
									<hr>
									@if ($errors->count() !== 0)
				<div class="alert alert-danger">
					<ul>
						@foreach ($errors->all() as $error)
							<li>{{$error}}</li>
						@endforeach
					</ul>
				</div>
			@endif
			@if (!empty($integration))
				<p>{!! $integration !!}</p>
			@endif

			@if (!empty($domainIntegration))
				<p>{!! $domainIntegration !!}</p>
			@endif
								</div>
								<div class="col-xs-12">
									@include('Orders.orderViews.partials.package-links', [
										'package' => $package
									])
								</div>

	              <div class="mt-4 bottom center">
									@if(!$package->is_outofstock)
	    						<button href="{{ route('order.group.package', [
	                            	$group,
	                            	$package->id
	                        	]) }}" class="btn btn-success">Add To Cart</button>
									@endif

	    						<input type="hidden" name="_token" value="{{ csrf_token() }}">
	    			            <input type="hidden" name="group" value="{{ $group->id }}">
	    			            <input type="hidden" name="package" value="{{ $package ? $package->id : '' }}">
	    					</div>

	            </div>
	          </div>
	        </div>
	        <!-- /.card-body -->
	      </div>
	      <!-- /.card -->
			</form>
	    </section>
	    <!-- /.content -->
</div>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.4/fotorama.js"></script>
@stop
