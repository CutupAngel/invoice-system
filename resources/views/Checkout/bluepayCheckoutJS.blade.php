<script type="text/javascript" src="https://secure.bluepay.com/v3/bluepay.js"></script>
<script type="text/javascript">
	function startPayment()
	{
		if($('[name="paymentMethod[type]"]:checked').val() == '0')
		{
			BluePay.setCredentials("{{$bluepayAccId}}", "{!! $bluepayApiSignature !!}");
			var expmonth = $('#ccMonth').val();
			if(expmonth.length < 2)
			{
				expmonth = '0' + expmonth;
			}
			var expyear = $('#ccYear').val();
			expyear = expyear.substring(2);

			var obData = {
				'CARD_ACCOUNT':$('input[data-bluepay="number"]').val(),
				'CARD_CVV2':$('input[data-bluepay="cvc"]').val(),
				'EXPMO':expmonth,
				'EXPYR':expyear
			};
			if({{$bluepayTestMode}} == 1)
			{
				obData.MODE = 'TEST';
			}
			BluePay.createToken(obData, bluepayCallback);
		}
		else if($('[name="paymentMethod[type]"]:checked').val() == '1')
		{
			BluePay.setCredentials("{{$bluepayAccId}}", "{{$bluepayApiSignature}}");
			var obData = {
				'PAYMENT_TYPE':'ACH',
				'ACH_ACCOUNT':$('input[name="paymentMethod[account]"]').val(),
				'ACH_ROUTING':$('input[name="paymentMethod[routing]"]').val()
			};
			if({{$bluepayTestMode}} == 1)
			{
				obData.MODE = 'TEST';
			}
			BluePay.createToken(obData, bluepayCallback);
		}
		else
		{
			console.log('no');
			submitData();
		}
	}

	function bluepayCallback(data,errors)
	{
	    console.log('errors: ', errors);
		if(typeof data == 'object')
		{
			if($('[name="paymentMethod[type]"]:checked').val() == 0)
			{
				var last4 = $('input[data-bluepay="number"]').val();
				last4 = last4.substr(last4.length - 4);
				if($('input[name=token]').length)
				{
					$('input[name="paymentMethod[token]"]').val(data.TRANS_ID);
					$('input[name="paymentMethod[last4]"]').val(last4);
					$('input[name="paymentMethod[expMonth]"]').val($('#ccMonth').val());
					$('input[name="paymentMethod[expYear]"]').val($('#ccYear').val());
				}
				else
				{
					$('#frmCheckout').append('<input type="hidden" name="paymentMethod[token]" value="' + data.TRANS_ID + '"/>');
					$('#frmCheckout').append('<input type="hidden" name="paymentMethod[last4]" value="' + last4 + '"/>');
					$('#frmCheckout').append('<input type="hidden" name="paymentMethod[expMonth]" value="' + $('#ccMonth').val() + '"/>');
					$('#frmCheckout').append('<input type="hidden" name="paymentMethod[expYear]" value="' + $('#ccYear').val() + '"/>');
				}
			}
			else
			{
				var last4 = $('input[name="paymentMethod[account]"]').val();
				last4 = last4.substr(last4.length - 4);
				$('input[name="paymentMethod[expMonth]"]').remove();
				$('input[name="paymentMethod[expYear]"]').remove();
				if($('input[name=token]').length)
				{
					$('input[name="paymentMethod[token]"]').val(data.TRANS_ID);
					$('input[name="paymentMethod[last4]"]').val(last4);
				}
				else
				{
					$('#frmCheckout').append('<input type="hidden" name="paymentMethod[token]" value="' + data.TRANS_ID + '"/>');
					$('#frmCheckout').append('<input type="hidden" name="paymentMethod[last4]" value="' + last4 + '"/>');
				}
			}
			submitData();
		}
	}

</script>
