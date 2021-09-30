@extends('Common.frontendLayout')
@section('title', 'Fraud Detected')
@section('content')
<div class="jumbotron">
	<div class="container">

		<div class="content-header">
			@if($fraudlabs_status == 'REVIEW')
				REVIEW REASON:
				 {{ $fraudlabs_result['fraudlabspro_message'] }}
			@elseif($fraudlabs_status == "REJECT")
				Account has been flagged for fraud, please contact our support team
			@endif
		</div><!-- content-header -->
	</div><!-- center -->
</div>
@stop
