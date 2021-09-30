@extends ('Common.template')
@section ('css')
<style>
   .vpsmenu .power-off, .power-off {
   color: #ef5350;
   }
   .refresh {
   color: #398bf7;
   }
   .suspend {
   color: #656373;
   }
   .cursor {
    cursor: pointer;
   }
</style>
@stop
@section('content')

@if (session('status'))
		<div class="alert alert-success">
				{{ session('status') }}
        @php session()->forget('status'); @endphp
		</div>
@endif

@if (session('error'))
		<div class="alert alert-danger">
				{{ session('error') }}
        @php session()->forget('error'); @endphp
		</div>
@endif

<div class="card">
   <div class="card-header">
      <div class="card-title">
         Virtualizor
      </div>
   </div>
   <div class="card-body">
      <div class="row">
         <div class="col-sm-12 table-responsive">
            <table class="table table-bordered table-striped p-0">
               <thead>
                  <tr>
                     <th>Hostname</th>
                     <th>Primary IP</th>
                     <th>Status</th>
                  </tr>
               </thead>
               <tbody>
                  <td>
                    @php
                      if(isset($vpsDetail['hostname']))
                      {
                          echo $vpsDetail['hostname'];
                      }
                    @endphp
                  </td>
                  @php
                    $ip = '';
                    if(isset($vpsDetail['ips']))
                    {
                        foreach($vpsDetail['ips'] as $ipNum)
                        {
                            $ip = $ipNum;
                            break;
                        }
                    }
                  @endphp
                  <td>{{ $ip }}</td>
                  <td>
                    @if(isset($vpsStatus['status']))
                        @if($vpsStatus['status'] == '1')
                          Online
                        @endif
                        @if($vpsStatus['status'] == '0')
                          Offline
                        @endif
                        @if($vpsStatus['status'] == '2')
                          Suspended
                        @endif
                    @endif
                  </td>
               </tbody>
            </table>
         </div>
      </div>
      <h3 class="text-center">VPS Information</h3>
      <div class="row">
         <div class="col-sm-3">
            <div class="card card-outline card-primary h-100">
               <!-- /.card-header -->
               <div class="card-body text-center">
                  <img src="https://my.nexusbytes.com/modules/servers/virtualizor/ui/images/centos_60.png">
                  <br>
                  <p>
                    @php
                      if(isset($vpsDetail['os_name']))
                      {
                          echo $vpsDetail['os_name'];
                      }
                    @endphp
                  </p>
               </div>
               <!-- /.card-body -->
            </div>
         </div>
         <div class="col-sm-3">
            <div class="card card-outline card-primary h-100">
               <!-- /.card-header -->
               <div class="card-body text-center">
                  <img src="https://my.nexusbytes.com/modules/servers/virtualizor/ui/images/flags/de.png">
                  <p>Falkenstein, Vogtlandkreis</p>
               </div>
               <!-- /.card-body -->
            </div>
         </div>
         <div class="col-sm-3">
            <div class="card card-outline card-primary h-100">
               <!-- /.card-header -->
               <div class="card-body">
                  Status:
                  @if(isset($vpsStatus['status']))
                      @if($vpsStatus['status'] == '1')
                        Online
                      @endif
                      @if($vpsStatus['status'] == '0')
                        Offline
                      @endif
                      @if($vpsStatus['status'] == '2')
                        Suspended
                      @endif
                  @endif
                  <br>
                  IP Address: {{ $ip }}<br>
                  Hostname:
                  @php
                    if(isset($vpsDetail['hostname']))
                    {
                        echo $vpsDetail['hostname'];
                    }
                  @endphp
               </div>
               <!-- /.card-body -->
            </div>
         </div>
         <div class="col-sm-3">
            <div class="card card-outline card-primary h-100">
               <!-- /.card-header -->
               <div class="card-body text-center pb-5">
                  <i class="fa fa-stop fa-3x suspend cursor mr-5 command" data-command="stop" aria-hidden="true"></i> <i id="restartimg" class="fa fa-sync fa-3x refresh mr-5 cursor command" data-command="restart" aria-hidden="true"></i> <i id="poweroffimg" class="fa fa-power-off fa-3x power-off cursor command" data-command="poweroff" aria-hidden="true"></i>
               </div>
               <!-- /.card-body -->
            </div>
         </div>
      </div>
      <div class="row mt-3">
         <div class="col-sm-3">
            <div class="card card-outline card-primary h-100">
               <!-- /.card-header -->
               <div class="card-body text-center">
                  <div style="display:inline;width:90px;height:90px;"><input type="text" class="knob" value="@php if(isset($vpsStatus['used_disk'])) { echo $vpsStatus['used_disk']; } @endphp" data-width="90" data-height="90" data-fgcolor="#00c0ef" style="width: 49px; height: 30px; position: absolute; vertical-align: middle; margin-top: 30px; margin-left: -69px; border: 0px; background: none; font: bold 18px Arial; text-align: center; color: rgb(0, 192, 239); padding: 0px; appearance: none;"></div>
                  <div class="knob-label">Disk Space</div>
               </div>
               <!-- /.card-body -->
            </div>
         </div>
         <div class="col-sm-3">
            <div class="card card-outline card-primary h-100">
               <!-- /.card-header -->
               <div class="card-body text-center">
                  <div style="display:inline;width:90px;height:90px;"><input type="text" class="knob" value="@php if(isset($vpsStatus['used_bandwidth'])) { echo $vpsStatus['used_bandwidth']; } @endphp" data-min="-150" data-max="150" data-width="90" data-height="90" data-fgcolor="#00a65a" style="width: 49px; height: 30px; position: absolute; vertical-align: middle; margin-top: 30px; margin-left: -69px; border: 0px; background: none; font: bold 18px Arial; text-align: center; color: rgb(0, 166, 90); padding: 0px; appearance: none;"></div>
                  <div class="knob-label">Bandwidth</div>
               </div>
               <!-- /.card-body -->
            </div>
         </div>
         <div class="col-sm-3">
            <div class="card card-outline card-primary h-100">
               <!-- /.card-header -->
               <div class="card-body text-center">
                  <div style="display:inline;width:90px;height:90px;"><input type="text" class="knob" value="@php if(isset($vpsStatus['used_cpu'])) { echo $vpsStatus['used_cpu']; } @endphp" data-width="90" data-height="90" data-fgcolor="#00c0ef" style="width: 49px; height: 30px; position: absolute; vertical-align: middle; margin-top: 30px; margin-left: -69px; border: 0px; background: none; font: bold 18px Arial; text-align: center; color: rgb(0, 192, 239); padding: 0px; appearance: none;"></div>
                  <div class="knob-label">CPU</div>
               </div>
               <!-- /.card-body -->
            </div>
         </div>
         <div class="col-sm-3">
            <div class="card card-outline card-primary h-100">
               <!-- /.card-header -->
               <div class="card-body text-center">
                  <div style="display:inline;width:90px;height:90px;"><input type="text" class="knob" value="@php if(isset($vpsStatus['used_ram'])) { echo $vpsStatus['used_ram']; } @endphp" data-width="90" data-height="90" data-fgcolor="#00c0ef" style="width: 49px; height: 30px; position: absolute; vertical-align: middle; margin-top: 30px; margin-left: -69px; border: 0px; background: none; font: bold 18px Arial; text-align: center; color: rgb(0, 192, 239); padding: 0px; appearance: none;"></div>
                  <div class="knob-label">RAM</div>
               </div>
               <!-- /.card-body -->
            </div>
         </div>
      </div>
      <div class="row mt-3">
         <div class="col-sm-3">
            <div class="card card-outline card-success h-100">
               <!-- /.card-header -->
               <div class="card-body text-center">
                 @if(isset($vpsStatus['status']))
                  <h5>Reinstall VPS</h5>
                 @else
                  <h5>Install VPS</h5>
                 @endif
                  <div class="form-group">
                     <label>Select OS</label>
                     <select id="osid" class="form-control">
                       @foreach ($oses as $os)
                           <option value="{{ $os['osid'] }}">{{ $os['name'] }}</option>
                       @endforeach
                     </select>
                  </div>
                  <div class="form-group">
                     <label>New Hostname</label>
                     <input type="text" class="form-control" id="hostname" name="hostname" placeholder="New Hostname" autocomplete="off">
                  </div>

                  @if(isset($vpsStatus['status']))
                      <button type="button" class="btn btn-success float-right ml-2 command" data-command="updateHostname" @php if($vpsDetail['rescue']) echo 'disabled';  @endphp>Update Hostname</button>
                      <button type="button" class="btn btn-danger float-right command" data-command="reinstall" @php if($vpsDetail['rescue']) echo 'disabled';  @endphp>Reinstall</button>
                  @endif
               </div>
               <!-- /.card-body -->
            </div>
         </div>
         <div class="col-sm-3">
            <div class="card card-outline card-success h-100">
               <!-- /.card-header -->
               <div class="card-body text-center">
                  @if(isset($vpsStatus['status']))
                    <h5>Change Password</h5>
                  @else
                    <h5>Set New Password</h5>
                  @endif
                  <div class="form-group">
                     <label>New Password</label>
                     <input type="text" id="new_password" class="form-control" placeholder="New Password" autocomplete="off">
                  </div>
                  <div class="form-group">
                     <label>Retype New Password</label>
                     <input type="text" id="confirm_new_password" class="form-control" placeholder="Retype New Password" autocomplete="off">
                  </div>

                  @if(isset($vpsStatus['status']))
                      <button type="button" class="btn btn-success float-right command" data-command="changePassword" @php if($vpsDetail['rescue']) echo 'disabled';  @endphp>Change Password</button>
                  @endif
               </div>
               <!-- /.card-body -->
            </div>
         </div>

         @if(isset($vpsStatus['status']))
         <div class="col-sm-3">
            <div class="card card-outline card-success h-100">
               <!-- /.card-header -->
               <div class="card-body text-center">
                  <h5>Rescue Mode</h5>
                  <div class="form-group">
                     <label>Root Password</label>
                     <input type="text" id="root_password" class="form-control" placeholder="Root Password" autocomplete="off">
                  </div>
                  <div class="form-group">
                     <label>Confirm Root Password</label>
                     <input type="text" id="confirm_root_password" class="form-control" placeholder="Confirm Root Password" autocomplete="off">
                  </div>
                  <button type="button" class="btn btn-primary float-right command" data-command="enableRescue" @php if($vpsDetail['rescue']) echo 'disabled';  @endphp>Enable Rescue Mode</button>
               </div>
               <!-- /.card-body -->
            </div>
         </div>
         @endif
         <div class="col-sm-3">
            <div class="card card-outline card-success h-100">
               <!-- /.card-header -->
               <div class="card-body text-center">
                  <img src="https://i.b-cdn.uk/virt-logo.png" width="90%">
                  <p>Login to the Enduser Panel</p>
                  @if(Auth::User()->isAdmin() || Auth::User()->isStaff() || Auth::User()->isClient())
                  <button class="btn btn-primary command mr-2" data-command="create_from_view">Create</button>
                      @if(isset($vpsStatus['status']))
                          @if($vpsStatus['status'] == '0' || $vpsStatus['status'] == '1')
                          <button class="btn btn-warning command mr-2" data-command="suspend">Suspend</button>
                          @endif
                          @if($vpsStatus['status'] == '2')
                          <button class="btn btn-warning command" data-command="unsuspend">Unsuspend</button>
                          @endif
                          <button class="btn btn-danger command mt-2" data-command="terminate">Terminate</button>
                      @endif
                  @endif
               </div>
               <!-- /.card-body -->
            </div>
         </div>
      </div>
   </div>
</div>
@stop
@section('javascript')
<!-- jQuery Knob -->
<script src="https://v2.b-cdn.uk/new-admin/plugins/jquery-knob/jquery.knob.min.js"></script>
<script>
   $(function () {
     /* jQueryKnob */

     $('.knob').knob({
       /*change : function (value) {
        //console.log("change : " + value);
        },
        release : function (value) {
        console.log("release : " + value);
        },
        cancel : function () {
        console.log("cancel : " + this.value);
        },*/
       draw: function () {

         // "tron" case
         if (this.$.data('skin') == 'tron') {

           var a   = this.angle(this.cv)  // Angle
             ,
               sa  = this.startAngle          // Previous start angle
             ,
               sat = this.startAngle         // Start angle
             ,
               ea                            // Previous end angle
             ,
               eat = sat + a                 // End angle
             ,
               r   = true

           this.g.lineWidth = this.lineWidth

           this.o.cursor
           && (sat = eat - 0.3)
           && (eat = eat + 0.3)

           if (this.o.displayPrevious) {
             ea = this.startAngle + this.angle(this.value)
             this.o.cursor
             && (sa = ea - 0.3)
             && (ea = ea + 0.3)
             this.g.beginPath()
             this.g.strokeStyle = this.previousColor
             this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false)
             this.g.stroke()
           }

           this.g.beginPath()
           this.g.strokeStyle = r ? this.o.fgColor : this.fgColor
           this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false)
           this.g.stroke()

           this.g.lineWidth = 2
           this.g.beginPath()
           this.g.strokeStyle = this.o.fgColor
           this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false)
           this.g.stroke()

           return false
         }
       }
     })
     /* END JQUERY KNOB */
   });

   (function($) {
     $('.command').on('click', function() {
       var $self = $(this);
       var command = $self.data('command');
       var params = "command=" + $self.data('command');
       if(command == "updateHostname")
       {
          if($('#hostname').val() == '')
          {
              alert('Please fill a hostname.');
              return;
          }
          params += "&hostname=" + $('#hostname').val();
       }
       if(command == "changePassword")
       {
          if($('#new_password').val() == '' || $('#confirm_new_password').val() == '')
          {
              alert('Please enter a new password and confirm it.');
              return;
          }
          if($('#new_password').val() != $('#confirm_new_password').val())
          {
              alert('New Password must be same with retype new password.');
              return;
          }
          params += "&new_password=" + $('#new_password').val();
       }
       if(command == "enableRescue")
       {
          if($('#root_password').val() == '' || $('#confirm_root_password').val() == '')
          {
              alert('Please fill a root password dan confirm root password.');
              return;
          }
          if($('#root_password').val() != $('#confirm_root_password').val())
          {
              alert('Root Password must be same with confirm root password.');
              return;
          }
          params += "&root_password=" + $('#root_password').val();
       }

       if(command == "reinstall")
       {
          if($('#new_password').val() == '' || $('#confirm_new_password').val() == '')
          {
              alert('Please fill a new password dan confirm password.');
              return;
          }
          if($('#new_password').val() != $('#confirm_new_password').val())
          {
              alert('New Password must be same with retype new password.');
              return;
          }
          params += "&new_password=" + $('#new_password').val() + "&osid=" + $('#osid').val();
       }
       if(command == "create_from_view")
       {
          if($('#hostname').val() == '')
          {
              alert('Please fill a hostname.');
              return;
          }
          if($('#new_password').val() == '' || $('#confirm_new_password').val() == '')
          {
              alert('Please fill a new password dan confirm password.');
              return;
          }
          if($('#new_password').val() != $('#confirm_new_password').val())
          {
              alert('New Password must be same with retype new password.');
              return;
          }
          params += "&hostname=" + $('#hostname').val() + "&new_password=" + $('#new_password').val() + "&osid=" + $('#osid').val();
       }

       $.ajax({
         url: window.location + '/command',
         type: 'PUT',
         dataType: 'JSON',
         data: params
       })
       .always(function(data) {
         $self.prop('disabled', true);
         location.reload();
       });
     });
   }(jQuery));

</script>
@stop
