@extends ('Common.template')

@section('title', 'Customers')

@section('page.title', 'Customers')
@section('page.subtitle', 'View')

@section('content')

@if (session('status'))
		<div class="alert alert-success">
				{{ session('status') }}
		</div>
@endif

@if (session('error'))
		<div class="alert alert-danger">
				{{ session('error') }}
		</div>
@endif

<div id="ajaxMessageSuccess" class="alert alert-success" style="display:none;">
</div>

<div class="row">
	<div class="col-md-6">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title">
					{{ trans('backend.cust-welcome') }}
				</h3>
					</div>
					<div class="card-body">
						<div class="col-lg-6 col-md-12">
							<div class="form-group">
								<label for="name">{{ trans('backend.cust-name') }}</label>
								{{ @$customer->name }}
							</div>
							<div class="form-group">
								<label for="email_address">{{ trans('backend.cust-email') }}</label>
								{{ $customer->email }}
							</div>
							<div class="form-group">
								<label for="web_address">{{ trans('backend.cust-web') }}</label>
								{{ $customer->mailingContact->address->website }}
							</div>
							<div class="form-group">
								<label for="username">{{ trans('backend.cust-username') }}</label>
								{{ @$customer->username }}
							</div>
						</div>
						<div class="col-lg-6 col-md-12">
							<div class="form-group">
								<label for="contact_name">{{ trans('backend.cust-business') }}</label>
								{{ $customer->mailingContact->address->business_name }}
							</div>
							<div class="form-group">
								<label for="address_1">{{ trans('backend.cust-address1') }}</label>
								{{ $customer->mailingContact->address->address_1 }}
							</div>
							<div class="form-group">
								<label for="address_2">{{ trans('backend.cust-address2') }}</label>
								{{ $customer->mailingContact->address->address_2 }}
							</div>
							<div class="form-group">
								<label for="city">{{ trans('backend.cust-city') }}</label>
								{{ $customer->mailingContact->address->city }}
							</div>
							<div class="form-group">
								<label for="county">{{ trans('backend.cust-state') }}</label>
								@if ($customer->mailingContact->address->county)
									{{ $customer->mailingContact->address->county->name }}
								@endif
							</div>
							<div class="form-group">
								<label for="country">{{ trans('backend.cust-country') }}</label>
								@if ($customer->mailingContact->address->country)
									{{ @$customer->mailingContact->address->country->name }}
								@endif
							</div>
							<div class="form-group">
								<label for="postal">{{ trans('backend.cust-postal') }}</label>
								{{ $customer->mailingContact->address->postal_code }}
							</div>
							<div class="form-group">
								<label for="telephone">{{ trans('backend.cust-telephone') }}</label>
								{{ $customer->mailingContact->address->phone }}
							</div>
							<div class="form-group">
								<label for="fax">{{ trans('backend.cust-fax') }}</label>
								{{ $customer->mailingContact->address->fax }}
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-6">
			<div class="card">
				<div class="card-header">
					<h3 class="card-title">
						{{ trans('backend.cust-other') }}
					</h3>
					</div>
					<div class="card-body">
						<li><i class="fa fa-file-text-o"></i> <a href="/admin/invoices/create/{{ $customer->id }}">{{ trans('backend.cust-invoice') }}</a></li>
						<li><i class="fa fa-sign-in"></i> <a href="/customers/{{ $customer->id }}/impersonate">{{ trans('backend.cust-login') }} {{@$customer->name}}</a></li>
						<li><i class="fa fa-compress"></i> <a href="#" class="merge-link">{{ trans('backend.cust-merge') }}</a></li>
						<!--<li><i class="fa fa-shopping-cart"></i> <a href="#">{{ trans('backend.cust-upgrade') }}</a></li>-->
						<li><i class="fa fa-money"></i> <a href="#" class="credit-link" data-toggle="modal" data-target="#creditModal">{{ trans('backend.cust-creditaccount') }}</a></li>
					</div>
				</div>

				@if($userAdmin->getSetting('integration.fraudlabs'))
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">
							FraudLabs Pro
						</h3>
					</div>
					<div class="card-body">
						Current Status: {{ $customer->fraudlabs_status ?? 'REVIEW' }}
						<li><i class="fa fa-file-text-o"></i> <a href="{{ route('set_customer_fraudlabs_status', ['customers' => $customer->id, 'status' => 'APPROVE']) }}">Approve</a></li>
						<li><i class="fa fa-sign-in"></i> <a href="{{ route('set_customer_fraudlabs_status', ['customers' => $customer->id, 'status' => 'REJECT']) }}">Reject</a></li>
						<li><i class="fa fa-sign-in"></i> <a href="{{ route('set_customer_fraudlabs_status', ['customers' => $customer->id, 'status' => 'REJECT_BLACKLIST']) }}">Reject Blacklist</a></li>
					</div>
				</div>
				@endif

			<div class="card">
				<div class="card-header">
					<h3 class="card-title">
						{{ trans('backend.cust-account') }}
					</h3>
					</div>
					<div class="card-body">
						<div class="form-group">
							<label for="paid-invoices">{{ trans('backend.cust-paid') }}</label>
							{{$stats['paid']}}
						</div>
						<div class="form-group">
							<label for="invoices-due">{{ trans('backend.cust-due') }}</label>
							{{$stats['due']}}
						</div>
						<div class="form-group">
							<label for="overdue-invoices">{{ trans('backend.cust-overdue') }}</label>
							{{$stats['overdue']}}
						</div>
						<div class="form-group">
							<label for="credit-balance">{{ trans('backend.cust-credit') }}</label>
							<span id="credit">{{ $stats['credit'] }}</span>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">
							{{ trans('backend.cust-notes') }}
						</h3>
						</div>
					<div class="card-body">
						<div class="form-group">
							<label for="comment">{{ trans('backend.cust-notessecond') }}</label>
							<textarea class="form-control" rows="5" id="comment">{{$notes}}</textarea>
						</div>
						<div class="form-group">
								<a href="#" class="btn btn-primary float-right" onclick="saveNote();">Save</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="card">
			<div class="card-header">
				<h3 class="card-title">
					Active Orders
				</h3>
			</div>
			<div class="card-body">
				<div class="row">
				<div class="col-sm-12 table-responsive">
						<table id="order-table" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>ID</th>
									<th>Package</th>
									<th>Price</th>
									<th>Start Date</th>
									<th>Last Invoice</th>
									<th>Cycle</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($customer->orders as $order)
									<tr>
										<td><a href="/customers/order/{{$order->id}}">{{$order->id}}</a></td>
										<td>{{$order->package->name}}</td>
										<td>{{$order->price}}</td>
										<td>{{$order->created_at}}</td>
										<td>{{$order->last_invoice}}</td>
										<td>{{$order->cycle->cycle()}}</td>
										<td>{{$order->statusText }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="box-footer text-right">
			<button id="edit" class="btn btn-primary">{{ trans('backend.cust-edit') }}</button>
			<button id="delete" class="btn btn-danger">{{ trans('backend.cust-delete') }}</button>
		</div>

	<div class="modal fade" id="creditModal" tabindex="-1" role="dialog" aria-labelledby="creditModal">
		<form method="post" class="credit-form-modal" action="/customers/{{ $customer->id }}/credit">
			 {{ csrf_field() }}
		  <div class="modal-dialog">
		    <div class="modal-content">
		        <div class="modal-header">
		          <h4 class="modal-title">
		            Credit Account
		          </h4>
							<button type="button" class="close" data-dismiss="modal">×</button>
		        </div>
		        <div class="modal-body text-center">
		          <div class="form-group">
		            <label for="credits">Credit Amount</label>
		            <input type="number" name="credit" id="credits" class="form-control credits" value="{{ $stats['credit'] }}" required step="any" placeholder="0.00">
		          </div>
		        </div>
		        <div class="modal-footer">
		          <button type="button" class="btn btn-default float-left btn-lrg" data-dismiss="modal">Cancel</button>
		          <button class="btn btn-success btn-lrg" type="submit">Save</button>
		        </div>
		    </div>
		  </div>
		</form>
	</div>
@stop

@section('javascript')

<script type="text/x-handlebars-template" id="merge-template">
	<div class="modal fade">
		<form method="post" class="merge-form-modal" action="#">
		  <div class="modal-dialog">
		    <div class="modal-content">
		        <div class="modal-header">
		          <h4 class="modal-title">
		            {{ trans('backend.cust-mergeaccount') }}
		          </h4>
							<button type="button" class="close" data-dismiss="modal">×</button>
		        </div>
		        <div class="modal-body text-center">
		          @{{#if errors}}
		            <div class="alert alert-danger">
		              @{{#each errors}}
		                <div>@{{this}}</div>
		              @{{/each}}
		            </div>
		          @{{/if}}
		          <div class="form-group">
		          	<label for="account">{{ trans('backend.cust-mergeinto') }} </label>
		            <select class="form-control" name="account" id="account">
									<option value="">-- Select Customer to Merge --</option>
		            	@{{#each customers}}
		            		<option value="@{{id}}">@{{name}}</option>
		            	@{{/each}}
		            </select>
		          </div>
		        </div>
		        <div class="modal-footer">
		          <button type="button" class="btn btn-default float-left btn-lrg mb-3" data-dismiss="modal">{{ trans('backend.cust-cancel') }}</button>
		          <button type="submit" class="btn btn-success btn-lrg mb-3">{{ trans('backend.cust-save') }}</button>
		        </div>
		    </div>
		  </div>
		</form>
	</div>
</script>

<script>

function saveNote()
{
	$.ajax({
			url: '/customers/save-note',
			type: 'POST',
			data: {
							note: $('textarea#comment').val(),
							customer_id: '{{ $customer->id }}'
			},
			success: function(result)
			{
					if(result.success)
					{
							$('#ajaxMessageSuccess').html(result.status);
							$('#ajaxMessageSuccess').attr('style', '');
					}
			}
		});
}

	(function($) {
		$('#edit').on('click', function() {
			window.location.pathname += '/edit';
		});

		$('#delete').on('click', function() {
			if (confirm("Are you sure you want to delete this customer?")) {
				$.ajax({
					url: window.location,
					type: 'DELETE'
				})
				.always(function() {
					window.location.pathname = '/customers';
				});
			}
		});

		$('body').on('submit', '.credit-form-modal', function() {
			var $modal = $(this).parent();
			var $self = $(this);

	        $.ajax({
	          url: '/customers/{{$customer->id}}/credit',
	          type: 'POST',
	          dataType: 'JSON',
	          data: $self.serialize()
	        })
	        .success(function(data) {
	        	$modal.modal('hide');
						$('#credit').text(parseFloat(data).toFixed(2));
						$('.credits').val(data);
						console.log(data)
	        }).fail(function(xhr) {
						$modal.modal('hide');

						var vars = {
						'credit': $self.find('#credit').val(),
						'errors': xhr.responseJSON
						}

						$(Handlebars.compile($('#credit-template').html())({
						vars
						})).modal();
	        });

	        return false;
		});

		$('.merge-link').on('click', function() {
			$.get('/helper/customers', function(customers) {
				$(Handlebars.compile($('#merge-template').html())({
					'customers': JSON.parse(customers)
				})).modal();
			});

			return false;
		})

		$('body').on('submit', '.merge-form-modal', function() {
			var $modal = $(this).parent();
			var $self = $(this);

			$.post('/customers/{{$customer->id}}/merge', $self.serialize(), function(data) {
				window.location = "/customers/" + data;
			}).fail(function(xhr) {
				console.log(xhr);
				$.get('/helper/customers', function(customers) {
					$modal.modal('hide');

					$(Handlebars.compile($('#merge-template').html())({
						'customers': JSON.parse(customers),
						'errors': xhr.responseJSON
					})).modal();
				});
			});

			return false;
		});

		$('#order-table').dataTable();
	})(jQuery);
</script>
@stop
