<!-- <div class="form-group">
	<label for="directadmin.package">Package:</label>
	<select name="directadmin[package]" id="directadmin.package" class="form-control" required>
		<option></option>
		foreach ($packages['list'] as $package)
			<option value="{{-- $package --}}">{{-- $package --}}</option>
		endforeach
	</select>
</div> -->

<div class="form-group">
	<label>DirectAdmin Servers</label>
	<select name="directadmin[server]" id="directadmin_server" class="form-control">
	@foreach($packages as $package)
			<option value="{{ $package['group'] }}" style="font-weight: bold;">Group: {{ $package['group'] }}</option>
		@foreach($package['integrations'] as $integration)
			<option value="{{ $integration->id }}">&nbsp;&nbsp;{{ $integration->hostname }}</option>
		@endforeach
	@endforeach
	</select>
</div>
