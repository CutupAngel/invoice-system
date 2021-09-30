@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Integrations')

@section('breadcrumbs')
  <a href="/settings/integrations">Integrations</a>
	<li class="breadcrumb-item active">DirectAdmin</li>
@stop

@section('content')
<div class="card">
  <div class="card-body">
    <table id="invoiceList" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Domain</th>
          <th>Primary IP</th>
          <th>Username</th>
          <th>Package</th>
          <th>Status</th>
          <th>Created</th>
        </tr>
      </thead>
      <tbody>
          @foreach($accounts as $account)
          <tr>
            <td>{{ $account['domain'] }}</td>
            <td>{{ $account['ip'] }}</td>
            <td>{{ $account['username'] }}</td>
            <td>{{ $account['package'] }}</td>
            <td>Active</td>
            <td>{{ $account['date_created'] }}</td>
          </tr>
          @endforeach
      </tbody>
    </table>
  </div>
</div>
@stop
