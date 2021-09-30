@extends('Common.template')

@section('page.title', 'Dashboard')
@section('page.subtitle', 'Control Panel')

@section('breadcrumbs')
	<li class="active">Dashboard</li>
@stop

@section('content')
  @foreach ($subscriptionAlerts as $alert)
    <div class="alert {{$alert['class']}}">
      {{$alert['text']}}
    </div>
  @endforeach

	<!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-2 col-xs-2" onclick="location.href='/admin/invoices/view/unpaid';" style="cursor: pointer;">
            <!-- small box -->
						<div class="small-box bg-info">
			        <div class="inner">
			          <h3>{{ $invoices_due }}</h3>
			          <p>Unpaid Invoices</p>
			        </div>
			        <div class="icon">
			          <i class="fa fa-files-o"></i>
			        </div>
			        <a href="/admin/invoices/view/unpaid" class="small-box-footer">View Unpaid Invoices <i class="fa fa-arrow-circle-right"></i></a>
			      </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-2">
      <!-- small box -->
			<div class="small-box bg-danger" onclick="location.href='/admin/invoices/view/overdue';" style="cursor: pointer;">
				<div class="inner">
					<h3>{{ $invoices_overdue }}</h3>
					<p>Overdue Invoices</p>
				</div>
				<div class="icon">
					<i class="fa fa-files-o"></i>
				</div>
				<a href="/admin/invoices/view/overdue" class="small-box-footer">View Overdue Invoices <i class="fa fa-arrow-circle-right"></i></a>
			</div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-2">
      <!-- small box -->
			<div class="small-box bg-dark" onclick="location.href='/customers';" style="cursor: pointer;">
				<div class="inner">
					<h3>{{ $customers }}</h3>
					<p>Number of Customers</p>
				</div>
				<div class="icon">
					<i class="fa fa-files-o"></i>
				</div>
				<a href="/customers" class="small-box-footer">View Customers <i class="fa fa-arrow-circle-right"></i></a>
			</div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-2">
      <!-- small box -->
			<div class="small-box bg-light" onclick="location.href='/reports/annual-sales';" style="cursor: pointer;">
				<div class="inner">
					<h3>{!! $currency->symbol !!}{{ $monthly_income }}</h3>
					<p>Monthly Income</p>
				</div>
				<div class="icon">
					<i class="fa fa-files-o"></i>
				</div>
				<a href="/reports/annual-sales" class="small-box-footer">View Reports <i class="fa fa-arrow-circle-right"></i></a>
			</div>
    </div><!-- ./col -->
		<div class="col-lg-2 col-xs-2">
      <!-- small box -->
			<div class="small-box bg-warning" onclick="location.href='/reports/annual-sales';" style="cursor: pointer;">
				<div class="inner">
					<h3>{!! $currency->symbol !!}{{ $yearly_income }}</h3>
					<p>Yearly Income</p>
				</div>
				<div class="icon">
					<i class="fa fa-files-o"></i>
				</div>
				<a href="/reports/annual-sales" class="small-box-footer">View Reports <i class="fa fa-arrow-circle-right"></i></a>
			</div>
    </div><!-- ./col -->
		<div class="col-lg-2 col-xs-2">
      <!-- small box -->
			<div class="small-box bg-info" onclick="location.href='/support/tickets';" style="cursor: pointer;">
				<div class="inner">
					<h3>{{ $support }}</h3>
					<p>Open Support Tickets</p>
				</div>
				<div class="icon">
					<i class="fa fa-files-o"></i>
				</div>
				<a href="/support/tickets" class="small-box-footer">View Tickets <i class="fa fa-arrow-circle-right"></i></a>
			</div>
    </div><!-- ./col -->
  </div><!-- /.row -->

  <!-- Main row -->
  <div class="row">
    <!-- Left col -->
    <section class="col-lg-7 connectedSortable">
			<div class="card">
          <div class="card-header">
            <h3 class="card-title">
          	<i class="fa fa-chart-pie mr-1"></i>
          	{{ trans('backend.db-sales') }}
					</h3>
        </div>
        <div class="card-body">
          <div class="chart tab-pane active" id="revenue-chart" style="position: relative; height: 300px;"></div>
        </div>
      </div>

      <!-- BillingServ News -->
			<div class="card">
          <div class="card-header">
            <h3 class="card-title">
          <i class="fa fa-newspaper-o"></i>
          {{ trans('backend.db-billingserv') }}</h3>
        </div>
        <div id="divRss"></div>
      </div>
      <!-- BillingServ News -->
    </section><!-- /.Left col -->

    <!-- right col (We are only adding the ID to make the widgets sortable)-->
    <section class="col-lg-5 connectedSortable">

         <!-- Featured Partners -->
				 <div class="card">
	           <div class="card-header">
	             <h3 class="card-title">
          		{{ trans('backend.db-partners') }}</h3>
        </div>
				<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
				  <ol class="carousel-indicators">
				    <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
				    <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
				  </ol>

  <!-- Wrapper for slides -->
	<div class="carousel-inner">
    <div class="carousel-item active">
      <a href="https://onlineapp.bluepay.com/interfaces/create_form/shortapp/00df37c6b1dc4e093b7573f1d836eb3c" target="_blank"><img src="https://bsv2.b-cdn.net/dist/img/BluePay.png" class="img-responsive" alt="BluePay"></a>
    </div>
    <div class="carousel-item">
      <a href="https://business.worldpay.com/partner/billingserv-ta-ecommerce-solutions-ltd" target="_blank"><img src="https://bsv2.b-cdn.net/dist/img/WorldPay.png" class="img-responsive" alt="WorldPay"></a>
    </div>
  </div>
</div>
      </div>
      <!-- BillingServ Partners -->

    </section><!-- right col -->
  </div><!-- /.row (main row) -->

@if(!$foundAddress)
<div class="modal fade" tabindex="-1" role="dialog" id="getstarted">
	<form id="form_getstarted">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <input type="hidden" name="address_type" value="0">
	      <div class="modal-header">
	        <h4 class="modal-title">First things first!</h4>
	      </div>
	      <div class="modal-body">
					<p>To get started with BillingServ please fill out the details below!</p>
	          <div class="alert alert-danger" id="div_error">
	          </div>
	        <div class="row">
	          <section class="col-lg-6">
	            <div class="form-group">
	              <label for="contact_name">Contact Name</label>
	              <input type="text" class="form-control" name="contact_name" id="contact_name" placeholder="Contact Name" value="{{ $address->contact_name }}" required="">
	            </div>
	          </section>
	          <section class="col-lg-6">
	            <div class="form-group">
	              <label for="business_name">Business Name</label>
	              <input type="text" class="form-control" name="business_name" id="business_name" placeholder="Business Name" value="{{ $address->business_name }}" required="">
	            </div>
	          </section>
	        </div>
	        <div class="row">
	          <section class="col-lg-6">
	            <div class="form-group">
	              <label for="phone">Telephone</label>
	              <input type="text" class="form-control" name="phone" id="phone" placeholder="Telephone" value="{{ $address->phone }}" required="">
	            </div>
	          </section>
	          <section class="col-lg-6">
	            <div class="form-group">
	              <label for="fax">Fax</label>
	              <input type="text" class="form-control" name="fax" id="fax" placeholder="Fax" value="{{ $address->fax }}">
	            </div>
	          </section>
	        </div>
	        <div class="form-group">
	          <label for="email">Email Address</label>
	          <input type="text" class="form-control" name="email" id="email" placeholder="Email Address" value="{{ $address->email }}" required="">
	        </div>
	        <div class="form-group">
	          <label for="address_1">Address 1</label>
	          <input type="text" class="form-control" name="address_1" id="address_1" placeholder="Address 1" value="{{ $address->address_1 }}" required="">
	        </div>
	        <div class="form-group">
	          <label for="address_2">Address 2</label>
	          <input type="text" class="form-control" name="address_2" id="address_2" placeholder="Address 2" value="{{ $address->address_2 }}">
	        </div>
	        <div class="form-group">
	          <label for="address_3">Address 3</label>
	          <input type="text" class="form-control" name="address_3" id="address_3" placeholder="Address 3" value="{{ $address->address_3 }}">
	        </div>
	        <div class="form-group">
	          <label for="address_4">Address 4</label>
	          <input type="text" class="form-control" name="address_4" id="address_4" placeholder="Address 3" value="{{ $address->address_4 }}">
	        </div>
	        <div class="row">
	          <section class="col-lg-6">
	            <div class="form-group">
	              <label for="city">City</label>
	              <input type="text" class="form-control" name="city" id="city" required value="{{ $address->city }}" placeholder="City"/>
	            </div>
	          </section>
	          <section class="col-lg-6">
	            <div class="form-group">
	              <label for="county_id">County/State</label>
	              <select class="selRegion form-control" name="county_id" id="county_id" required>
	                <option disabled="" selected="">Please select a county</option>
	              </select>
	            </div>
	          </section>
	        </div>
	        <div class="row">
	          <section class="col-lg-6">
	            <div class="form-group">
	              <label for="country_id">Country</label>
	              <select class="selCountry form-control" name="country_id" id="country_id" required>
	                <option disabled="" selected="">Please select</option>
	                @foreach ($countries as $country)
	                  <option value="{{$country->id}}">{{$country->name}}</option>
	                @endforeach
	              </select>
	            </div>
	          </section>
	          <section class="col-lg-6">
	            <div class="form-group">
	              <label for="postal_code">Postcode/Zip</label>
	              <input type="text" class="form-control" name="postal_code" id="postal_code" value="{{ $address->postal_code }}" placeholder="Postcode/Zip"/>
	            </div>
	          </section>
	        </div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" id="btn_submit" class="btn btn-success"><i class="fa fa-pencil-square-o"></i> Complete</button>
	      </div>
	    </div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</form>
</div><!-- /.modal -->
@endif

@stop

@section('css')
<style>
img {
    vertical-align: middle;
    max-width: 100%;
    height: auto;
    width: auto\9;
}
.feedEkList img {
    display: none;
}
.feedEkList{list-style:none outside none;background-color:#FFFFFF; padding:4px 18px; color:#3E3E3E;}
.feedEkList li{border-bottom:1px solid #D3CAD7; padding:5px;}
.feedEkList li:last-child{border-bottom:none;}
.itemTitle a{font-weight:bold; color:#4EBAFF !important; text-decoration:none }
.itemTitle a:hover{ text-decoration:underline }
.itemDate{font-size:11px;color:#AAAAAA;}

.datepicker{z-index:1151 !important;}

.ideal-image-slider {
	position: relative;
	overflow: hidden;
}
.iis-slide {
	display: block;
	bottom: 0;
	text-decoration: none;
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background-repeat: no-repeat;
	background-position: 50% 50%;
	background-size: cover;
	text-indent: -9999px;
}

/* Slide effect */
.iis-effect-slide .iis-slide {
	opacity: 0;
	-webkit-transition-property: -webkit-transform;
	   -moz-transition-property: -moz-transform;
		 -o-transition-property: -o-transform;
			transition-property: transform;
	-webkit-transition-timing-function: ease-out;
	   -moz-transition-timing-function: ease-out;
		 -o-transition-timing-function: ease-out;
			transition-timing-function: ease-out;
	-webkit-transform: translateX(0%);
		-ms-transform: translateX(0%);
			transform: translateX(0%);
}
.iis-effect-slide .iis-current-slide {
	opacity: 1;
	z-index: 1;
}
.iis-effect-slide .iis-previous-slide {
	-webkit-transform: translateX(-100%);
		-ms-transform: translateX(-100%);
			transform: translateX(-100%);
}
.iis-effect-slide .iis-next-slide {
	-webkit-transform: translateX(100%);
		-ms-transform: translateX(100%);
			transform: translateX(100%);
}
.iis-effect-slide.iis-direction-next .iis-previous-slide,
.iis-effect-slide.iis-direction-previous .iis-next-slide { opacity: 1; }

/* Touch styles */
.iis-touch-enabled .iis-slide { z-index: 1; }
.iis-touch-enabled .iis-current-slide { z-index: 2; }
.iis-touch-enabled.iis-is-touching .iis-previous-slide,
.iis-touch-enabled.iis-is-touching .iis-next-slide { opacity: 1; }

/* Fade effect */
.iis-effect-fade .iis-slide {
	-webkit-transition-property: opacity;
	   -moz-transition-property: opacity;
		 -o-transition-property: opacity;
			transition-property: opacity;
	-webkit-transition-timing-function: ease-in;
	   -moz-transition-timing-function: ease-in;
		 -o-transition-timing-function: ease-in;
			transition-timing-function: ease-in;
	opacity: 0;
}
.iis-effect-fade .iis-current-slide {
	opacity: 1;
	z-index: 1;
}

</style>
@stop

@section('javascript')
  <script src="https://bsv2.b-cdn.net/dist/js/ideal-image-slider.js"></script>
  <script>
    var slider = new IdealImageSlider.Slider({
    selector: '#slider',
    height: 256, // Required but can be set by CSS
    interval: 4000
});
slider.start();
  </script>
<script type="text/javascript" src="https://bsv2.b-cdn.net/FeedEk/js/FeedEk.min.js"></script>

@if(!$foundAddress && !auth()->user()->isStaff())
<script type="text/javascript">
		$(window).on('load', function(){
        $('#getstarted').modal('show');

				$('#country_id').on('change', function() {
					var country = $(this).val();
					if (country.length === 0) {
						return false;
					}

					$.ajax({
						url: '/helper/counties/' + country,
						dataType: 'json',
						success: function(counties) {
			        $('#county_id').empty();
							$('#county_id').append($('<option></option>').attr('value', '').text('Please select a county'));
							$.each(counties, function(i, county) {
								$('#county_id').append($('<option></option>').attr('value', county.id).text(county.name));
							});
							$('#county_id').prop('disabled', false);
			        $('#county_id').val("{{ old('county_id') }}");
						}
					});
				}).trigger('change');
    });

    $('#getstarted .alert').hide();

	$('#btn_submit').on('click', function() {

    $.ajax({
      headers: { 'X-CSRF-Token': '{{ csrf_token() }}'},
      url: '/settings/my-account',
      type: 'POST',
      dataType: 'json',
      data: $('#form_getstarted').serialize()+'&action=contacts',
			success: function(data) {
      		//$('#getstarted').modal('hide');

					if(!data.success)
					{
						$('#div_error').html('');
						$.each(data.errors, function(index, val) {
							$('#div_error').append('<li>' + index + ': ' + val + '</li>');
							$('#div_error').attr('style', '');
						});
					}
					else
					{
							$('#getstarted').modal('hide');
					}
    	},
    	fail: function(data) {
		      var errors = data.responseJSON;

		      var $ul = $('<ul></ul>');

		      $.each(errors, function(index, val) {
		        $ul.append($('<li></li>').text(val));
		      });

		      $div.find('ul').replaceWith($ul);

		      $div.show();
		      $('#btn_submit').addClass('fa-pencil-square-o').removeClass('fa-spin fa-refresh fa-check');
		      $('#btn_submit').removeClass('btn-success').addClass('btn-danger').prop('disabled', false);
    	}
  	});
	});

  $('.collapse').on('shown.bs.collapse', function(){
    $(this).parent().find(".box-header .glyphicon-plus").removeClass("glyphicon-plus").addClass("glyphicon-minus");
  }).on('hidden.bs.collapse', function(){
    $(this).parent().find(".box-header .glyphicon-minus").removeClass("glyphicon-minus").addClass("glyphicon-plus");
  });

</script>
@endif

  <script type="text/x-handlebars-template" id="todo-form-template">
      <div class="modal fade todo-form-modal">
        <form method="post" action="#">
          <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">×</button>
                  <h4 class="modal-title">
                    @{{#if edit}}
                      Edit Item
                    @{{else}}
                      Add Item
                    @{{/if}}
                  </h4>
                </div>
                <div class="modal-body text-center">
                  @{{#if errors}}
                    <div class="alert alert-danger">
                      @{{#each errors}}
                        <div>@{{this}}</div>
                      @{{/each}}
                    </div>
                  @{{/if}}
                  @{{#if edit}}
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" value="@{{i}}">
                  @{{/if}}
                  <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" value="@{{title}}" required>
                  </div>
                  <div class="form-group">
                    <label for="date">Date</label>
                    <input name="date" id="date" value="@{{date}}" data-provide="datepicker">
                  </div>
                  <div class="form-group checkbox">
                    <label>
                      <input type="checkbox" name="checked" value="1" @{{#if checked}}checked@{{/if}}>
                      Completed
                    </label>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default float-left btn-lrg" data-dismiss="modal">Cancel</button>
                  @{{#if edit}}
                    <button type="submit" class="btn btn-success btn-lrg">Edit</button>
                  @{{else}}
                    <button type="submit" class="btn btn-success btn-lrg">Add</button>
                  @{{/if}}
                </div>
            </div>
          </div>
        </form>
      </div>
  </script>

  <script type="text/x-handlebars-template" id="todo-row-template">
    <li data-id="@{{id}}">
      <input type="checkbox" @{{#if checked}}CHECKED@{{/if}}>
      <span class="text">@{{title}}</span>
      @{{#if duedate}}
        <small class="label label-default"><i class="fa fa-clock-o"></i> <span class="date">@{{duedate}}</span></small>
      @{{/if}}
      <div class="tools">
        <i class="fa fa-edit"></i>
        <i class="fa fa-trash-o"></i>
      </div>
    </li>
  </script>

  <script>
    $('#divRss').FeedEk({
      FeedUrl: 'https://blog.billingserv.com/rss/',
      MaxCount: 5,
      ShowDesc: true,
      DescCharacterLimit:200,
      TitleLinkTarget:'_blank',
      ShowPubDate: true
    });

    (function($) {
      var revenueChart = new Morris.Area({
        element: 'revenue-chart',
        resize: true,
        data: {!! json_encode($revenueChart) !!},
        xkey: 'y',
        ykeys: ['completed', 'refunded'],
        labels: ['Sold', 'Refunded'],
        lineColors: ['#a0d0e0', '#3c8dbc'],
        hideHover: 'auto'
      });

      $('.box ul.nav a').on('shown.bs.tab', function() {
        revenueChart.redraw();
      })

      //The Calender
      $("#calendar").datepicker();

      $('#todo-add').on('click', function() {
        $(Handlebars.compile($('#todo-form-template').html())()).modal('show');
        $("#date").datepicker();
      });

      $('body').on('submit', '.todo-form-modal form', function() {
        var $modal = $(this).parent();
        var $self = $(this);

        $self.find('button').prop('disable', true);

        $.post('/todo', $self.serialize(), function(data, textStatus, xhr) {
          $(Handlebars.compile($('#todo-row-template').html())({
            id: data,
            title: $self.find('[name="title"]').val(),
            duedate: $self.find('[name="date"]').val(),
            checked: $self.find('[name="checked"]').prop('checked'),
          })).appendTo('#todo-list');
          $modal.modal('hide');
        }).fail(function(xhr) {
          $modal.modal('hide');

          var vars = {
            edit: $self.find('[name="id"]').length,
            i: $self.find('[name="id"]').val(),
            title: $self.find('[name="title"]').val(),
            date: $self.find('[name="date"]').val(),
            checked: $self.find('[name="checked"]').prop('checked'),
            errors: xhr.responseJSON
          };

          $(Handlebars.compile($('#todo-form-template').html())(vars)).modal('show');
        });

        return false;
      });

      $('body').on('click', '.todo-list .fa-edit', function () {
        $self = $(this).parents('li');
        $(Handlebars.compile($('#todo-form-template').html())({
          edit: true,
          i: $self.data('id'),
          title: $self.find('.text').text(),
          date: $self.find('.date').text(),
          checked: $self.find('[type="checkbox"]').prop('checked')
        })).modal('show');
      });

      $('body').on('change', '.todo-list [type="checkbox"]', function() {
        $self = $(this).parents('li');
        $.ajax({
          url: '/todo',
          type: 'PUT',
          dataType: 'JSON',
          data: {
            id: $self.data('id'),
            title: $self.find('.text').text(),
            date: $self.find('.date').text(),
            checked: $self.find('[type="checkbox"]').prop('checked')
          }
        })
      });

      $('body').on('click', '.todo-list .fa-trash-o', function () {
        $self = $(this).parents('li');
        $.ajax({
          url: '/todo',
          type: 'DELETE',
          dataType: 'JSON',
          data: {id: $self.data('id')}
        })
        .done(function() {
          $self.remove();
        });
      });

      $('body').on('hidden.bs.modal', '.modal', function () {
        $(this).data('bs.modal', null);
      });

      var todoList = {!! json_encode($todo) !!};
      $(todoList).each(function(index, el) {
        el.id = index;
        $(Handlebars.compile($('#todo-row-template').html())(el)).appendTo('#todo-list');
      });
    })(jQuery);
  </script>

@stop
