@php
		$type_0 = 0;
@endphp

@foreach ($package->link as $i => $link)
	<?php $option = $link->option;?>
	@if ($option->type == 0)
		@php $type_0++; @endphp
		@if($type_0 % 2 == 1)
			<div class="row">
		@endif
				<div class="col-xs-6">
						{{ $option->display_name }}
						<select class="form-control" name="options[]">
							@foreach ($option->values as $k => $value)
								@if ($value->fee > 0)
									<option value="{{ $value->id }}">
										{{ $value->display_name }} {!! $currency->symbol !!}{{ number_format($value->price / $default_currency->conversion * $currency->conversion, 2) }} {{ $value->cycle() }} {!! $currency->symbol !!}{{ number_format($value->fee / $default_currency->conversion * $currency->conversion, 2) }}
									</option>
								@else
									<option value="{{ $value->id }}">
										{{ $value->display_name }} {!! $currency->symbol !!}{{ number_format($value->price / $default_currency->conversion * $currency->conversion, 2) }} {{ $value->cycle() }}
									</option>
								@endif
							@endforeach
						</select>
				</div>
		@if($type_0 % 2 == 0)
			</div>
		@endif
	@elseif ($option->type == 1)
		@php $type_0++; @endphp
		@if($type_0 % 2 == 1)
			<div class="row">
		@endif
				<div class="col-xs-6">
					<label>{{ $option->display_name }}</label>
					<br/>
						@foreach ($option->values as $value)
							@if (!empty($value->display_name))
								{{ $value->display_name }}
							@endif
							<input class="form-control" type="text" name="inputs[][{{ $value->id }}]">
						@endforeach
					</div>
		@if($type_0 % 2 == 0)
			</div>
		@endif
	@elseif($option->type == 2)
		@php $type_0++; @endphp
		@if($type_0 % 2 == 1)
			<div class="row">
		@endif
				<div class="col-xs-6">
					<label>{{ $option->display_name }}</label>
					<br/>
						@foreach ($option->values as $value)
							@if (!empty($value->display_name))
								{{ $value->display_name }}
							@endif
							<input class="form-control" type="number" name="numeric[][{{ $value->id }}]">
						@endforeach
					</div>
		@if($type_0 % 2 == 0)
			</div>
		@endif
	@elseif ($option->type == 3)
		@php $type_0++; @endphp
		@if($type_0 % 2 == 1)
			<div class="row">
		@endif
				<div class="col-xs-6">
					<label>{{ $option->display_name }}</label>
					<br/>
						@foreach($option->values as $value)
							@if ($value->fee > 0)
								<div class="radio">
						      <label for="option-{{ $option->id }}">
										<input type="radio" name="radio[][{{ $value->id }}]" id="option-{{ $option->id }}" value=""> {{ $value->display_name }} {!! $currency->symbol !!}{{ number_format($value->price / $default_currency->conversion * $currency->conversion, 2) }} {{ $value->cycle() }} {!! $currency->symbol !!}{{ number_format($value->fee * $currency->conversion, 2) }}
									</label>
						    </div>
							@else
							<div class="radio">
								<label for="option-{{ $option->id }}">
									<input type="radio" name="radio[][{{ $value->id }}]" id="option-{{ $option->id }}" value=""> {{ $value->display_name }} {!! $currency->symbol !!}{{ number_format($value->price / $default_currency->conversion * $currency->conversion, 2) }} {{ $value->cycle() }}
								</label>
							</div>
							@endif
						@endforeach
					</div>
		@if($type_0 % 2 == 0)
			</div>
		@endif
	@elseif($option->type == 4)
		@php $type_0++; @endphp
		@if($type_0 % 2 == 1)
			<div class="row">
		@endif
				<div class="col-xs-6">
					<label>{{ $option->display_name }}</label>
					<br/>
					@foreach($option->values as $value)
						@if ($value->fee > 0)
							<div class="checkbox">
								<label for="option-{{ $option->id }}">
									<input type="checkbox" class="order-checkbox" name="checkbox[]" value="{{ $value->id }}" id="option-{{ $option->id }}"> 
									{{ $value->display_name }} {!! $currency->symbol !!}{{ number_format($value->price / $default_currency->conversion * $currency->conversion, 2) }} {{ $value->cycle() }} {!! $currency->symbol !!}{{ number_format($value->fee * $currency->conversion, 2) }}
								</label>
							</div>
						@else
						<div class="checkbox">
							<label for="option-{{ $option->id }}">
								<input type="checkbox" class="order-checkbox" name="checkbox[]" value="{{ $value->id }}" id="option-{{ $option->id }}"> 
								{{ $value->display_name }} {!! $currency->symbol !!}{{ number_format($value->price / $default_currency->conversion * $currency->conversion, 2) }} {{ $value->cycle() }}
							</label>
						</div>
						@endif
					@endforeach
				</div>
		@if($type_0 % 2 == 0)
			</div>
		@endif
	@elseif($option->type == 5)
		@php $type_0++; @endphp
		@if($type_0 % 2 == 1)
			<div class="row">
		@endif
				<div class="col-xs-6">
					<label>{{ $option->display_name }}</label>
					<br/>
						@foreach($option->values as $value)
							@if ($value->fee > 0)
							<div class="checkbox">
								<span class="span-switch">
									{{ $value->display_name }} {!! $currency->symbol !!}{{ number_format($value->price / $default_currency->conversion * $currency->conversion, 2) }} {{ $value->cycle() }} {!! $currency->symbol !!}{{ number_format($value->fee * $currency->conversion, 2) }}
								</span>
								&nbsp;
								<label class="switch">
									<input type="checkbox" name="toogle[]" value="{{ $value->id }}" id="option-{{ $option->id }}">
									<div class="slider round">
										<span class="on">ON</span>
										<span class="off">OFF</span>
									</div>
								</label>
							</div>
							@else
							<div class="checkbox">
								<span class="span-switch">
									{{ $value->display_name }} {!! $currency->symbol !!}{{ number_format($value->price / $default_currency->conversion * $currency->conversion, 2) }} {{ $value->cycle() }}
								</span>
								&nbsp;
								<label class="switch">
									<input type="checkbox" name="toogle[]" value="{{ $value->id }}" id="option-{{ $option->id }}">
									<div class="slider round">
										<span class="on">ON</span>
										<span class="off">OFF</span>
									</div>
								</label>
							</div>
							@endif
						@endforeach
					</div>
		@if($type_0 % 2 == 0)
			</div>
		@endif
	@endif
@endforeach
