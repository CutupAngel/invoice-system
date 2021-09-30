@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Payment Gateways')

@section('breadcrumbs')
  <a href="/settings/paymentgateways">Payment Gateways</a>
  <li class="breadcrumb-item active">Merchant Focus</li>
@stop

@section('content')
  <div class="alert alert-success text-center">
    <h4>Merchant Focus</h4>

    Your application has been submitted.<br>
    You should recieve a notification via email or phone about your status.
  </div>
@stop
