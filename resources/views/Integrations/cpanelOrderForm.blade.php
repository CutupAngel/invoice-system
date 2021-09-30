<div class="panel panel-default">
	<div class="panel-heading">
		<h3>cPanel Settings</h3>
	</div>
	<div class="panel-body">
		@if (!empty($domainForm))
			@include($domainForm[0], $domainForm[1])
		@else
			<div class="form-group">
				<label for="cpanel.domain">Domain</label>
				<input type="text" name="cpanel[domain]" id="cpanel.domain" required>
			</div>
		@endif
	</div>
</div>
