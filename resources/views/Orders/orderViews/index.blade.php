@extends('Common.frontendLayout')
@section('title', 'Checkout')

@section('css')
<style>
.featured {
    border-color: #373f50!important;
    color: #000!important;
    background-color: #ddffff!important;
    border-left: 6px solid #373f50!important;
    padding: 0.01em 16px;
		margin-bottom: 20px;
}
</style>
@stop

@section('content')

<div class="jumbotron">
		<div class="content-header">
			<p><h1>Orders</h1></p>
		</div>

		<div class="content-content">
			<div class="row">
				<div class="col-md-12">
          @if($featuredPackages)
  					<div id="div_featured" class="featured">
              @foreach($featuredPackages as $featuredPackage)
                  <div id="div_package_{{ $featuredPackage->id }}"></div>
              @endforeach
  					</div>
          @endif
					<select id="select_order" class="form-control">
						<option value="">--Select Group--</option>
							@foreach($orderGroups as $orderGroup)
								<option value="{{ $orderGroup->id }}">{{ $orderGroup->name }}</option>
							@endforeach
					</select>
				</div>
			</div>
			<div class="row">
				<div id="div_packages" class="col-md-12">
				</div>
			</div>
		</div><!-- content-content -->
</div><!-- content -->
@stop

@section('js')
<script>

  $(function() {
    ShowPackagesFeatured();
  });


	$('#select_order').on('change', function() {
    ShowPackageFromGroup($(this).val());
	});

  function ShowPackageFromGroup(val)
  {
      if(val == '')
      {
          $('#div_packages').html('');
      }
      else
      {
          $.ajax({
            url: '/order/get_packages_from_group/' + val,
            dataType: 'json',
            success: function(packages) {
              if(packages == '')
              {
                  $('#div_packages').html('<h2>No Packages Found</h2>');
                  return;
              }

              $.each(packages, function(i, package) {
                  $('#div_packages').append('\
                                                <div class="col-md-6">\
                                                  <h3>' + package.name + '</h3>\
                                                  <p>' + package.description + '</p>\
                                                  <p>\
                                                    <a href="/order/' + package.group_id + '/' + package.id + '" class="btn btn-lg btn-success float:right;"><i class="fa fa-shopping-cart"></i> Order Now</a>\
                                                  </p>\
                                                </div>\
                                            ');

              });
            }
          });
      }
  }

  function ShowPackagesFeatured()
  {
      $.ajax({
        url: '/order/get_package_featured/',
        dataType: 'json',
        success: function(packages) {
          if(packages == '')
          {
              $('#div_featured').attr('style', 'display:none;');
              return;
          }

          $.each(packages, function(i, package) {
              $('#div_package_' + package.id).html('\
                                            <div class="col-md-6">\
                                              <h3>' + package.name + ' ({!! $currency->symbol !!}' + package.price + ')</h3>\
                                              <p>\
                                                <a href="/order/' + package.group_id + '/' + package.id + '" class="btn btn-lg btn-success float:right;"><i class="fa fa-shopping-cart"></i> Order Now</a>\
                                              </p>\
                                            </div>\
                                        ');

          });
        }
      });
  }

</script>
@endsection
