@extends('Common.frontendLayout')
@section('title', 'Please Wait...')

@section('css')
	<link rel="stylesheet" href="https://v2.b-cdn.uk/dist/css/checkout2.css">
@stop

@section('content')
<FORM id="frmPaypalStandard" ACTION="https://www.paypal.com/cgi-bin/webscr" METHOD="POST">
<INPUT TYPE="hidden" NAME="cmd" VALUE="_xclick">
<INPUT TYPE="hidden" NAME="business" VALUE="{{$userEmail}}">
<INPUT TYPE="hidden" NAME="undefined_quantity" VALUE="1">
<INPUT TYPE="hidden" NAME="item_name" VALUE="Invoice Number: {{$invoiceNumber}}">
<INPUT TYPE="hidden" NAME="amount" VALUE="{{$subTotal}}">
<INPUT TYPE="hidden" NAME="currency_code" VALUE="{{$currency}}">
<INPUT TYPE="hidden" NAME="first_name" VALUE="{{$fname}}">
<INPUT TYPE="hidden" NAME="last_name" VALUE="{{$lname}}">
<INPUT TYPE="hidden" NAME="address1" VALUE="{{$address1}}">
<INPUT TYPE="hidden" NAME="address2" VALUE="{{$address2}}">
<INPUT TYPE="hidden" NAME="city" VALUE="{{$city}}">
<INPUT TYPE="hidden" NAME="state" VALUE="{{$region}}">
<INPUT TYPE="hidden" NAME="zip" VALUE="{{$zip}}">
<INPUT TYPE="hidden" NAME="lc" VALUE="{{$lc}}">
<INPUT TYPE="hidden" NAME="email" VALUE="{{$customerEmail}}">
<INPUT TYPE="hidden" NAME="night_phone_a" VALUE="{{$phone1}}">
<INPUT TYPE="hidden" NAME="night_phone_b" VALUE="{{$phone2}}">
<INPUT TYPE="hidden" NAME="night_phone_c" VALUE="{{$phone3}}">
<INPUT TYPE="hidden" NAME="return" VALUE="{{$receipt}}">
</FORM>
@stop

@section('js')
<script type="text/javascript">
	$(document).ready(function(){
		$('#frmPaypalStandard').submit();
	});
</script>
@stop
