@extends('Common.frontendLayout')
@section('title', 'Checkout')

@section('content')

<div class="jumbotron">
		@if ($group)
		<div class="content-header">
			{{ $group->name }}
			<p>{!! $group->description !!}</p>
		</div>

		<div class="content-content">
			@if (sizeof($group->packages) > 0)
			<ul>
				@forelse($group->packages as $package)
				<li>
					<div class="top">
						<h1>{{ $package->name }}</h1>
					</div>

					<div class="middle">
						<p>
							@if($site('defaultCurrency') ==  $currency->id)
							{!! $currency->symbol !!}{{ $package->cycles->first()->price }}
							@else
							{!! $currency->symbol !!}{{ number_format($package->cycles->first()->price / $default_currency->conversion * $currency->conversion, 2) }}
							@endif
							<span class="cycleTerm">
							@if (sizeof($package->cycles) > 1)

							@else
								@if($package->cycles->first()->cycle === 1)
									{{ ($package->cycles->first()->fee > 0) ? '*' : '' }}
								@else
									/ {{ $package->cycles->first()->cycle() }} {{ ($package->cycles->first()->fee > 0) ? '' : '' }}
									@if ($package->cycles->first() && $package->cycles->first()->fee > 0)

									<h4>* Setup Fee {!! $currency->symbol !!} {{ number_format($package->cycles->first()->fee / $default_currency->conversion * $currency->conversion,2) }}</h4>
									@endif
								@endif
							@endif
							</span>
							<span>
								@if ($package->options && sizeof($package->options) > 0)
									<ul>
										@foreach ($package->options as $option)
										{{ dd($package->opt->toArray())}}
											<li>
												{{ $option->name }}:{{$option['desc']}} ({!! $currency->symbol !!}{{ $option['setup'] }})
											</li>
										@endforeach
									</ul>
								@endif
							</span>
						</p>
					</div><!-- middle -->

					<div class="bottom">
                        <a href="{{ route('order.group.package', [
                        	$group,
                        	$package->id
                    	]) }}" class="btn btn-success">Add To Cart</a>
					</div><!-- bottom -->
				</li>
				@empty
					There are no packages in this group.
				@endforelse
			</ul>
			@endif
		</div><!-- contetn-content -->
		@endif
</div><!-- content -->
@stop
