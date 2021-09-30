<div class="form-group">
	<label for="plesk.package">Package:</label>
	<select name="plesk[package]" id="plesk.package" class="form-control" required>
		<option></option>
		@foreach ($packages as $package)
			<option {{ $package !== $selected ? '': 'selected' }}>
				{{$package}}
			</option>
		@endforeach
	</select>
</div>
