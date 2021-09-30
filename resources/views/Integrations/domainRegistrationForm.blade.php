<ul class="nav nav-tabs">
	<li class="active">
		<a href="#ownDomain" data-toggle="tab">
			<label>
				<input type="radio" name="domain[option]" value="own" checked> Use My Own Domain
			</label>
		</a>
	</li>
	<li>
		<a href="#registerDomain" data-toggle="tab">
			<label>
				<input type="radio" name="domain[option]" value="register"> Register a new Domain
			</label>
		</a>
	</li>
	<li>
		<a href="#transferDomain" data-toggle="tab">
			<label>
				<input type="radio" name="domain[option]" value="transfer"> Transfer My Own Domain
			</label>
		</a>
	</li>
</ul>
<div class="tab-content">
	<div class="tab-pane active" id="ownDomain">
		<div class="form-group">
			<label for="domain.domain">Domain</label>
			<input type="text" name="domain[domain]" id="domain.domain" value="{{old('domain.domain')}}" required>
		</div>
	</div>
	<div class="tab-pane" id="registerDomain">
		<div class="form-group">
			<label for="domain.sld">Domain</label>
			<div class="input-group">
				<input type="text" name="domain[sld]" id="domain.sld" value="{{old('domain.sld')}}" required disabled>
				<span class="input-group-addon">
					<select name="domain[tld]" class="input-control" required disabled>
						@foreach ($tlds as $tld)
							<option value="{{$tld}}">.{{$tld}}</option>
						@endforeach
					</select>
				</span>
			</div>
		</div>
	</div>
	<div class="tab-pane" id="transferDomain">
		<div class="form-group">
			<label for="domain.sld">Domain</label>
			<div class="input-group">
				<input type="text" name="domain[sld]" id="domain.sld" value="{{old('domain.sld')}}" required disabled>
				<span class="input-group-addon">
					<select name="domain[tld]" class="input-control" required disabled>
						@foreach ($tlds as $tld)
							<option value="{{$tld}}">.{{$tld}}</option>
						@endforeach
					</select>
				</span>
			</div>
		</div>
	</div>
</div>

@section('js')
	<script>
		$('a[data-toggle="tab"]').on('shown.bs.tab', function() {
			$('.tab-pane').find('select, input').prop('disabled', true);
			$('.tab-pane.active').find('select, input').prop('disabled', false);
			$(this).find('input').prop('checked', true);
		});
	</script>
@stop

@section('css')
	<style>
		.tab-pane {
			display: none;
		}

		.tab-pane.active {
			display: block;
		}
	</style>
@stop