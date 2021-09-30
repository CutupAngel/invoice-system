<div class="form-group">
	<label for="virtualizor.package">Package:</label>
	<select name="virtualizor_package" id="virtualizor_package" class="form-control" required>
		<option></option>
		@foreach ($packages as $package)
			<option value="{{ $package['plid'] }}" {{ $package['plid'] == $selected ? 'selected' : '' }}>{{ $package['plan_name'] }}</option>
		@endforeach
	</select>
</div>
