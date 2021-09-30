@extends('Orders.orderView')

@section('integration')
  <div class="card card-default">
    <div class="card-header">DirectAdmin</div>
    <div class="card-body">
      <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Username</th>
            <th>Password</th>
            <th>Domain</th>
            <th>Package</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{@$settings['directadmin.username']}}</td>
            <td>{{@$settings['directadmin.password']}}</td>
            <td>{{@$settings['directadmin.domain']}}</td>
            <td>{{@$settings['directadmin.package']}}</td>
            <td>
              {{$settings['directadmin.statusText']}}
              @if (@$settings['directadmin.error'])
                <i class="fa fa-info-circle text-info" title="{{$settings['directadmin.error']}}"></i>
              @endif
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
    @if(Auth::User()->isAdmin() || Auth::User()->isStaff() || Auth::User()->isClient())
    <div class="card-footer text-right">
      @if (!in_array($settings['directadmin.status'], ['1', '2']))
        <button class="btn btn-primary command" data-command="create">Create</button>
      @endif
      @if ($settings['directadmin.status'] === '1')
        <button class="btn btn-warning command" data-command="suspend">Suspend</button>
      @elseif ($settings['directadmin.status'] === '2')
        <button class="btn btn-warning command" data-command="unsuspend">Unsuspend</button>
      @endif
      @if (in_array($settings['directadmin.status'], ['1', '2']))
        <button class="btn btn-danger command" data-command="terminate">Terminate</button>
      @endif
    </div>
    @endif
  </div>
@stop

@section('javascript')
  <script>
    (function($) {
      $ ('.command').on('click', function() {
        var $self = $(this);

        $.ajax({
          url: window.location + '/command',
          type: 'PUT',
          dataType: 'JSON',
          data: {command: $self.data('command')}
        })
        .always(function() {
          $self.prop('disabled', true);
          location.reload();
        });
      });
    }(jQuery));
  </script>
@stop
