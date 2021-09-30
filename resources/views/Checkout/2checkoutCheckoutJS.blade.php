<script type="text/javascript" src="https://www.2checkout.com/checkout/api/2co.min.js"></script>
<script type="text/javascript">
	$(document).ready(function()
	{
		TCO.loadPubKey({{$testmode}});
	});
	function startPayment()
	{
		if($('input[name="paymentMethod[type]"]:checked').val() == 0)
		{
			var args = {
				sellerId: "{{$sellerid}}",
				publishableKey: "{{$publishablekey}}",
				ccNo: $("input[data-2checkout='number']").val(),
				cvv: $("input[data-2checkout='cvc']").val(),
				expMonth: $("input[data-2checkout='exp-month']").val(),
				expYear: $("input[data-2checkout='exp-year']").val()
			};

			// Make the token request
			TCO.requestToken(TwocheckoutCallback, TwocheckoutCallback, args);
				$('#frmCheckout').submit();
		}
	}

	function TwocheckoutCallback(args)
	{ 
		$('#errMsgs').empty();
		if (response.errorCode) {
			$('#errMsgs').append('<p class="text-danger">'+response.errorMsg+'</p>');
		} else {
			var token = response.token.token;
			$('#frmCheckout').append('<input type="hidden" name="2checkoutToken" value="'+token+'"/>');
			submitData();
		}
	}

</script>
