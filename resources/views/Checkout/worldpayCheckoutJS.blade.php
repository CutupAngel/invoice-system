<script src="https://cdn.worldpay.com/v1/worldpay.js"></script>
<script type="text/javascript">
	function startPayment()
	{
		if($('input[name="paymentMethod[type]"]:checked').val() == 0)
		{
			var form = document.getElementById('frmCheckout');
			Worldpay.useOwnForm({
				'clientKey': '{{$worldpaykey}}',
				'form': form,
				'reusable': true,
				'callback': worldpayCallback
			});
			$('#frmCheckout').submit();
		}
	}

	function worldpayCallback(status, response)
	{
		$('#errMsgs').empty();
		if (response.error) {
			if(response.error.message)
			{
				$.each(response.error.message,function(k,v){
					$('#errMsgs').append('<p class="text-danger">'+v+'</p>');
					$('#errMsgs').attr('style', '');
				});
			}
            btnLoading(false);
		} else {
			var token = response.token;
			if($('[name="worldpayToken"]').length)
			{
				$('[name="worldpayToken"]').val(token);
			}
			else
			{
				$('#frmCheckout').append('<input type="hidden" name="worldpayToken" value="'+token+'"/>');
			}
			submitData();
		}
	}

</script>
