<div class="card card-default">
	<div class="card-heading">
		<h3>DirectAdmin Settings</h3>
	</div>
	<div class="card-body">
		@if (!empty($domainForm))
			@include($domainForm[0], $domainForm[1])
		@else
			<div class="form-group">
				<label for="directadmin.domain">Domain</label>
				<input type="text" class="form-control" name="directadmin[domain]" id="directadmin.domain" required>
			</div>
		@endif
	</div>
</div>
