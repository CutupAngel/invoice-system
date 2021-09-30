@extends('Common.template')

@section('page.title', 'Dashboard')
@section('page.subtitle', 'Control Panel')

@section('breadcrumbs')
  <li class="active">Dashboard</li>
@stop

@section('css')
<style>
div.dataTables_wrapper div.dataTables_info {
    /* padding-top: 0.85em; */
    white-space: nowrap;
    padding: 12px 12px 12px 12px !important;
}
div.dataTables_wrapper div.dataTables_paginate {
    margin: 0;
    white-space: nowrap;
    text-align: right;
    padding: 4px 10px 10px 10px !important;
}
div.dataTables_wrapper div.dataTables_length label {
    font-weight: normal;
    text-align: left;
    white-space: nowrap;
    padding: 12px 0px 0px 12px !important;
}
</style>
@stop

@section('content')

  <!-- Small boxes (Stat box) -->
  <div class="row">
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-yellow">
        <div class="inner">
          <h3>{{ $count['invoices'] }}</h3>
          <p>Due Invoices</p>
        </div>
        <div class="icon">
          <i class="fa fa-files-o"></i>
        </div>
        <a href="/invoices" class="small-box-footer">View Invoices <i class="fa fa-arrow-circle-right"></i></a>
      </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-red">
        <div class="inner">
          <h3>{{ $count['overdueInvoices'] }}</h3>
          <p>Overdue Invoices</p>
        </div>
        <div class="icon">
          <i class="fa fa-files-o"></i>
        </div>
        <a href="/invoices/view/overdue" class="small-box-footer">View Invoices <i class="fa fa-arrow-circle-right"></i></a>
      </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $count['services'] }}</h3>
          <p>Services</p>
        </div>
        <div class="icon">
          <i class="fa fa-files-o"></i>
        </div>
        <a href="/products-ordered" class="small-box-footer">Active Services <i class="fa fa-arrow-circle-right"></i></a>
      </div>
    </div><!-- ./col -->
  </div><!-- /.row -->
  <!-- Main row -->
  <div class="row">
    <!-- Left col -->
    <section class="col-lg-6 connectedSortable">
      <!-- Invoices -->
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">Invoices</h3>
        </div><!-- /.box-header -->
        <div class="card-body p-0">
          <table id="customerInvoices" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Invoices</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($invoices as $invoice)
                <tr data-id="{{ $invoice['id'] }}">
                  <td><a href="/invoices/{{ $invoice['id'] }}">{{ $invoice['invoice_number'] }} ({{ number_format($invoice['total'], 2) }}) {{ date('m/d/Y', strtotime($invoice['due_at'])) }}</a></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div><!-- /.box-body -->
      </div><!-- /.box -->
    </section><!-- /.Left col -->

    <!-- right col -->
    <section class="col-lg-6 connectedSortable">
      <!-- News -->
      <div class="card card-success">
        <div class="card-header">
          <h3 class="card-title">Active Services</h3>
        </div><!-- /.box-header -->
        <div class="card-body p-0">
          <table id="customerServices" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Active Services</th>
              </tr>
            </thead>
            <tbody>
               @foreach($orders as $k=>$order)
                <tr data-id="{{ $order->id }}">
                  <td>@if (Auth::User()->isCustomer())
                      <a href="/products-ordered/order/{{ $order->id }}">
                  @else
                      <a href="/orders/{{ $order->package->group->id }}">
                  @endif
                    {{ $order->package->name }}
                  </a></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div><!-- /.box-body -->
      </div><!-- /.box -->
    </section><!-- right col -->
  </div><!-- /.row (main row) -->
@stop

@section('javascript')
<script>
$('#customerInvoices').DataTable({
  'paging': true,
  'searching': false,
  'ordering': false,
  'pageLength': 5
});

$('#customerServices').DataTable({
  'paging': true,
  'searching': false,
  'ordering': false,
  'pageLength': 5
});
</script>
@stop
