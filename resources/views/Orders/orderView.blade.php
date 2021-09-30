@extends('Common.template')

@section('page.title', 'Order')

@section('content')
  <div class="card card-default">
    <div class="card-header">
      <h1 class="card-title">{{ $order->id }}: {{ $order->package ? $order->package->name : '' }}</h1>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-lg-6">
          <div class="card card-default">
            <div class="card-header">Order Information</div>
            <div class="card-body">
              @if($user && $order->customer && !$user->isCustomer())
                  @if ($order->customer)
                      Customer: <a href="/customers/{{$order->customer->id}}">{{$order->customer->name}}</a><br>
                  @endif
                  @if ($order->package)
                      Package: <a href="/orders/{{ $order->package->group->id }}/{{$order->package->id}}">{{ $order->package->name }}</a><br>
                  @endif
              @else
                  @if ($order->package)
                      Package: {{ $order->package->name }}<br>
                  @endif
              @endif
              Billing Cycle: {{$order->cycle->cycle()}} ({{$order->price}})<br>
              Status: {{$order->getStatusTextAttribute($order->status)}}
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          {{-- Invoices --}}
          <div class="card card-default">
            <div class="card-header">Invoices</div>
            <div class="card-body">
              <table class="table table-border table-hover table-responsive dataTable">
                <thead>
                  <tr>
        						<th class="sorting_asc col-md-1">Num.</th>
        						<th class="sorting col-md-2">Amount</th>
        						<th class="sorting col-md-2">Status</th>
        						<th class="sorting col-md-2">Due</th>
        					</tr>
                </thead>
                <tbody>
                  @foreach ($order->invoices as $invoice)
                    <tr>
                      <td>{{$invoice->invoice_number}}</td>
                      <td>{{$invoice->total}}</td>
                      <td>{{$invoice->status()}}</td>
                      <td>{{$invoice->due_at}}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card card-default">
            <div class="card-header">Your Downloads</div>
            <div class="card-body">
              @foreach ($order->package->files as $file)
                <a href="/products-ordered/order/download/{{ $file->id }}">{{$file->filename}}</a>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      @yield('integration')
    </div>
  </div>
@stop
