<div class="form-group">
	<label for="cpanel.package">Package:</label>
	<select name="cpanel_package" id="cpanel_package" class="form-control" required>
		<option></option>
		@foreach ($packages as $package)
			<option value="{{ $package['name'] }}" {{ $package['name'] == $selected ? 'selected' : '' }}>{{ $package['name'] }}</option>
		@endforeach
	</select>
</div>
