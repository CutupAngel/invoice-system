@extends('Orders::orderView')

@section('integration')
  <div class="panel panel-default">
    <div class="panel-heading">Plesk</div>
    <div class="panel-body">
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
            <td>{{$settings['plesk.username']}}</td>
            <td>{{$settings['plesk.password']}}</td>
            <td>{{$settings['plesk.domain']}}</td>
            <td>{{$settings['plesk.package']}}</td>
            <td>
              {{$settings['plesk.statusText']}}
              @if ($settings['plesk.error'])
                <i class="fa fa-info-circle text-info" title="{{$settings['plesk.error']}}"></a>
              @endif
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="panel-footer text-right">
      @if (!in_array($settings['plesk.status'], ['1', '2']))
        <button class="btn btn-primary command" data-command="create">Create</button>
      @endif
      @if ($settings['plesk.status'] === '1')
        <button class="btn btn-warning command" data-command="suspend">Suspend</button>
      @elseif ($settings['plesk.status'] === '2')
        <button class="btn btn-warning command" data-command="unsuspend">Unsuspend</button>
      @endif
      @if (in_array($settings['plesk.status'], ['1', '2']))
        <button class="btn btn-danger command" data-command="terminate">Terminate</button>
      @endif
    </div>
  </div>
@stop

@section('javascript')
  <script>
    (function($) {
      $('.command').on('click', function() {
        var $self = $(this);

        $.ajax({
          url: window.location + '/command',
          type: 'PUT',
          dataType: 'JSON',
          data: {command: $self.data('command')}
        })
        .always(function() {
          $self.prop('disabled', true);
        });
      });
    }(jQuery));
  </script>
@stop
