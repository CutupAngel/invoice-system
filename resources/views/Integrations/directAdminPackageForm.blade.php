<div class="form-group">
	<label for="directadmin.package">Package:</label>
	<select name="directadmin[package]" id="directadmin_package" class="form-control" required>
		<option></option>
		@foreach ($packages as $package)
			<option value="{{ $package }}">{{ $package }}</option>
		@endforeach
	</select>
</div>
