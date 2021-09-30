<div class="card card-default">
	<div class="card-heading">
		<h3><strong>Virtualizor Settings</strong></h3>
		<hr>
	</div>
	<div class="card-body">
			<div class="row">
					<div class="col-xs-6">
						<label for="virtualizor.os">OS</label>
						<select class="form-control" name="virtualizor[os]" id="virtualizor.os" required>
								@foreach ($oses as $os)
										<option value="{{ $os['osid'] }}">{{ $os['name'] }}</option>
								@endforeach
						</select>
					</div>
					<div class="col-xs-6">
						<label for="virtualizor.hostname">Hostname</label>
						<input type="text" class="form-control" name="virtualizor[hostname]" id="virtualizor.hostname" required />
					</div>
					<div class="col-xs-6">
						<label for="virtualizor.password">Password</label>
						<input type="text" class="form-control" name="virtualizor[password]" id="virtualizor.password" required />
					</div>
			</div>
	</div>
</div>
